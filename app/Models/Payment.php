<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\HasUuid;
use App\Modules\Invoices\Services\InvoicePaymentSyncService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'subscription_id',
    'invoice_id',
    'customer_id',
    'agency_id',
    'amount',
    'currency',
    'payment_method',
    'transaction_reference',
    'payment_date',
    'status',
    'notes',
])]
class Payment extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    private ?int $previousInvoiceId = null;

    protected static function booted(): void
    {
        static::saving(static function (Payment $payment): void {
            $payment->previousInvoiceId = $payment->exists && $payment->getOriginal('invoice_id') !== null
                ? (int) $payment->getOriginal('invoice_id')
                : null;

            if (blank($payment->subscription_id)) {
                return;
            }

            $subscription = Subscription::query()->find($payment->subscription_id);

            if ($subscription !== null) {
                $user = auth()->user();

                if ($user !== null && ! $user->isSuperAdmin() && $user->agency_id !== $subscription->agency_id) {
                    throw new AuthorizationException('The selected subscription does not belong to your agency.');
                }

                if (! blank($payment->invoice_id)) {
                    $invoice = Invoice::query()->find($payment->invoice_id);

                    if ($invoice !== null && (int) $invoice->subscription_id !== (int) $subscription->id) {
                        throw new AuthorizationException('The selected invoice does not match the subscription.');
                    }
                }

                $payment->customer_id = $subscription->customer_id;
                $payment->agency_id = $subscription->agency_id;
            }
        });

        static::saved(static function (Payment $payment): void {
            $payment->syncInvoiceBalance();
        });

        static::deleted(static function (Payment $payment): void {
            $payment->syncInvoiceBalance();
        });

        static::restored(static function (Payment $payment): void {
            $payment->syncInvoiceBalance();
        });
    }

    /**
     * @return BelongsTo<Subscription, Payment>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return BelongsTo<Invoice, Payment>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<Customer, Payment>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Agency, Payment>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    private function syncInvoiceBalance(): void
    {
        foreach (array_unique(array_filter([$this->previousInvoiceId, $this->invoice_id])) as $invoiceId) {
            $invoice = Invoice::query()->find($invoiceId);

            if ($invoice !== null) {
                app(InvoicePaymentSyncService::class)->sync($invoice);
            }
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'payment_date' => 'date',
            'status' => PaymentStatus::class,
        ];
    }
}

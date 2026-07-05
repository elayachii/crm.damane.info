<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\HasUuid;
use App\Modules\Invoices\Services\InvoiceCalculator;
use App\Modules\Invoices\Services\InvoiceNumberGenerator;
use App\Modules\Invoices\Services\InvoicePaymentSyncService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'invoice_number',
    'customer_id',
    'subscription_id',
    'agency_id',
    'issue_date',
    'due_date',
    'subtotal',
    'tax',
    'discount',
    'total',
    'balance_due',
    'currency',
    'status',
    'notes',
])]
class Invoice extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(static function (Invoice $invoice): void {
            if (blank($invoice->invoice_number)) {
                $invoice->invoice_number = app(InvoiceNumberGenerator::class)->generate();
            }
        });

        static::saving(static function (Invoice $invoice): void {
            if (! blank($invoice->subscription_id)) {
                $subscription = Subscription::query()->find($invoice->subscription_id);

                if ($subscription !== null) {
                    $user = auth()->user();

                    if ($user !== null && ! $user->isSuperAdmin() && $user->agency_id !== $subscription->agency_id) {
                        throw new AuthorizationException('The selected subscription does not belong to your agency.');
                    }

                    if (! blank($invoice->customer_id) && (int) $invoice->customer_id !== (int) $subscription->customer_id) {
                        throw new AuthorizationException('The selected customer does not match the subscription.');
                    }

                    $invoice->customer_id = $subscription->customer_id;
                    $invoice->agency_id = $subscription->agency_id;
                }
            }

            app(InvoiceCalculator::class)->applyTotals($invoice);
        });

        static::saved(static function (Invoice $invoice): void {
            app(InvoicePaymentSyncService::class)->sync($invoice);
        });
    }

    /**
     * @return BelongsTo<Customer, Invoice>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Subscription, Invoice>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return BelongsTo<Agency, Invoice>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * @return HasMany<Payment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'status' => InvoiceStatus::class,
        ];
    }
}

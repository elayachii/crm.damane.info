<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\HasUuid;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'subscription_id',
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

    protected static function booted(): void
    {
        static::saving(static function (Payment $payment): void {
            if (blank($payment->subscription_id)) {
                return;
            }

            $subscription = Subscription::query()->find($payment->subscription_id);

            if ($subscription !== null) {
                $user = auth()->user();

                if ($user !== null && ! $user->isSuperAdmin() && $user->agency_id !== $subscription->agency_id) {
                    throw new AuthorizationException('The selected subscription does not belong to your agency.');
                }

                $payment->customer_id = $subscription->customer_id;
                $payment->agency_id = $subscription->agency_id;
            }
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

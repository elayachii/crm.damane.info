<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Models\Concerns\HasUuid;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'customer_id',
    'agency_id',
    'plan_name',
    'status',
    'start_date',
    'end_date',
    'price',
    'currency',
    'notes',
])]
class Subscription extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saving(static function (Subscription $subscription): void {
            if (blank($subscription->customer_id)) {
                return;
            }

            $customer = Customer::query()->find($subscription->customer_id);

            if ($customer !== null) {
                $user = auth()->user();

                if ($user !== null && ! $user->isSuperAdmin() && $user->agency_id !== $customer->agency_id) {
                    throw new AuthorizationException('The selected customer does not belong to your agency.');
                }

                $subscription->agency_id = $customer->agency_id;
            }
        });
    }

    /**
     * @return BelongsTo<Customer, Subscription>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Agency, Subscription>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * @return Attribute<int, never>
     */
    protected function remainingDays(): Attribute
    {
        return Attribute::get(function (): int {
            if ($this->end_date === null) {
                return 0;
            }

            return max(0, (int) now()->startOfDay()->diffInDays($this->end_date, false));
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
        ];
    }
}

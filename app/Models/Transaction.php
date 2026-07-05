<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\TransferOperator;
use App\Models\Concerns\HasUuid;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'customer_id',
    'user_id',
    'transaction_type',
    'operator',
    'transaction_reference',
    'sender_name',
    'receiver_name',
    'sender_country',
    'receiver_country',
    'amount',
    'currency',
    'fees',
    'total_amount',
    'status',
    'transaction_date',
    'notes',
])]
class Transaction extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(static function (Transaction $transaction): void {
            $user = auth()->user();

            if ($user !== null && blank($transaction->user_id)) {
                $transaction->user_id = $user->id;
            }

            if (blank($transaction->transaction_date)) {
                $transaction->transaction_date = now();
            }
        });

        static::saving(static function (Transaction $transaction): void {
            $transaction->assignAgency();
            $transaction->calculateTotalAmount();
        });
    }

    /**
     * @return BelongsTo<Agency, Transaction>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * @return BelongsTo<Customer, Transaction>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<User, Transaction>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    private function assignAgency(): void
    {
        $user = auth()->user();
        $customer = blank($this->customer_id) ? null : Customer::query()->find($this->customer_id);

        if ($customer !== null) {
            if ($user !== null && ! $user->isSuperAdmin() && $user->agency_id !== $customer->agency_id) {
                throw new AuthorizationException('The selected customer does not belong to your agency.');
            }

            $this->agency_id = $user?->isSuperAdmin() ? $customer->agency_id : ($user?->agency_id ?? $customer->agency_id);
        }

        if ($user !== null && ! $user->isSuperAdmin() && $this->agency_id !== $user->agency_id) {
            throw new AuthorizationException('Transactions can only be created inside your agency.');
        }
    }

    private function calculateTotalAmount(): void
    {
        $this->total_amount = round((float) $this->amount + (float) $this->fees, 2);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_type' => TransactionType::class,
            'operator' => TransferOperator::class,
            'amount' => 'decimal:2',
            'fees' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'status' => TransactionStatus::class,
            'transaction_date' => 'datetime',
        ];
    }
}

<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('subscription_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('agency_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('MAD');
            $table->string('payment_method', 30)->default(PaymentMethod::CASH->value);
            $table->string('transaction_reference')->nullable();
            $table->date('payment_date');
            $table->string('status', 30)->default(PaymentStatus::PENDING->value);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'status', 'deleted_at']);
            $table->index(['agency_id', 'payment_date']);
            $table->index(['subscription_id', 'deleted_at']);
            $table->index(['customer_id', 'deleted_at']);
            $table->index(['agency_id', 'transaction_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

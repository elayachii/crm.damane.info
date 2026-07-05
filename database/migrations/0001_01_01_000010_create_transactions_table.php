<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\TransferOperator;
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
        Schema::table('customers', static function (Blueprint $table): void {
            $table->string('national_id')->nullable()->after('phone');
            $table->index(['agency_id', 'national_id']);
        });

        Schema::create('transactions', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('agency_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('transaction_type', 30)->default(TransactionType::SEND_MONEY->value);
            $table->string('operator', 50)->default(TransferOperator::OTHER->value);
            $table->string('transaction_reference')->nullable();
            $table->string('sender_name');
            $table->string('receiver_name');
            $table->string('sender_country', 2)->default('MA');
            $table->string('receiver_country', 2);
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('MAD');
            $table->decimal('fees', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('status', 30)->default(TransactionStatus::PENDING->value);
            $table->timestamp('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'transaction_date']);
            $table->index(['agency_id', 'operator']);
            $table->index(['agency_id', 'status']);
            $table->index(['agency_id', 'transaction_type']);
            $table->index(['customer_id', 'deleted_at']);
            $table->index(['user_id', 'deleted_at']);
            $table->index(['agency_id', 'transaction_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');

        Schema::table('customers', static function (Blueprint $table): void {
            $table->dropIndex(['agency_id', 'national_id']);
            $table->dropColumn('national_id');
        });
    }
};

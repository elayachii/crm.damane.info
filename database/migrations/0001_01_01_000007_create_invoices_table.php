<?php

use App\Enums\InvoiceStatus;
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
        Schema::create('invoices', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('agency_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->string('currency', 3)->default('MAD');
            $table->string('status', 30)->default(InvoiceStatus::DRAFT->value);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'status', 'deleted_at']);
            $table->index(['agency_id', 'due_date']);
            $table->index(['agency_id', 'issue_date']);
            $table->index(['customer_id', 'deleted_at']);
            $table->index(['subscription_id', 'deleted_at']);
        });

        Schema::table('payments', static function (Blueprint $table): void {
            $table->foreignId('invoice_id')
                ->nullable()
                ->after('subscription_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['invoice_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', static function (Blueprint $table): void {
            $table->dropIndex(['invoice_id', 'deleted_at']);
            $table->dropConstrainedForeignId('invoice_id');
        });

        Schema::dropIfExists('invoices');
    }
};

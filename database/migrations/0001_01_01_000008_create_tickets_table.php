<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
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
        Schema::create('tickets', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('ticket_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('agency_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->string('priority', 30)->default(TicketPriority::MEDIUM->value);
            $table->string('status', 30)->default(TicketStatus::OPEN->value);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'status', 'deleted_at']);
            $table->index(['agency_id', 'priority']);
            $table->index(['agency_id', 'created_at']);
            $table->index(['customer_id', 'deleted_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['created_by', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

<?php

use App\Enums\CustomerStatus;
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
        Schema::create('customers', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('agency_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('full_name');
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('country', 2)->default('MA');
            $table->string('language', 10)->default('fr');
            $table->text('notes')->nullable();
            $table->string('status', 30)->default(CustomerStatus::ACTIVE->value);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'status', 'deleted_at']);
            $table->index(['agency_id', 'created_at']);
            $table->index(['agency_id', 'phone']);
            $table->index(['agency_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

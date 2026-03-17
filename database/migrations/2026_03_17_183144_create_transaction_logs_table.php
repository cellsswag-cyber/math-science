<?php

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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('deposit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('payment_id')->nullable()->index();
            $table->string('event_type', 80)->index();
            $table->string('source', 64)->default('system');
            $table->string('status', 32)->index();
            $table->string('reference')->nullable();
            $table->string('request_signature')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->unique(['deposit_id', 'event_type', 'reference'], 'transaction_logs_unique_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};

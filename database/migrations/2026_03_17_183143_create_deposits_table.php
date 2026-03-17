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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->decimal('crypto_amount', 20, 8);
            $table->decimal('paid_crypto_amount', 20, 8)->nullable();
            $table->decimal('credited_amount', 14, 2)->nullable();
            $table->string('currency', 20)->default('USDT');
            $table->string('price_currency', 10)->default('usd');
            $table->string('pay_currency', 40)->default('usdttrc20');
            $table->string('payment_id')->unique();
            $table->string('order_id')->unique();
            $table->string('wallet_address')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('status', 32)->index();
            $table->string('gateway_status', 64)->nullable()->index();
            $table->unsignedInteger('confirmations')->default(0);
            $table->unsignedTinyInteger('min_confirmations')->default(2);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('failed_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};

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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id', 50)->unique(); // Format: PAY-XXXXXXXXXXXX
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->enum('payment_method', ['card', 'wallet', 'bank_transfer', 'cash'])->default('card');
            $table->enum('gateway', ['paymob', 'internal', 'manual'])->default('paymob');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('AED');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_order_id')->nullable();
            $table->string('intention_id')->nullable(); // Paymob Intention API ID
            $table->json('gateway_response')->nullable();
            $table->json('gateway_metadata')->nullable();
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable(); // Model class name (polymorphic)
            $table->unsignedBigInteger('reference_id')->nullable(); // Related model ID
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'status']);
            $table->index(['wallet_id', 'status']);
            $table->index(['gateway', 'status']);
            $table->index(['gateway_transaction_id']);
            $table->index(['gateway_order_id']);
            $table->index(['intention_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['payment_id']);
            $table->index(['created_at']);
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('set null');
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

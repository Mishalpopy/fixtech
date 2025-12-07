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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->decimal('reserved_balance', 15, 2)->default(0.00); // For pending transactions
            $table->string('currency', 3)->default('AED');
            $table->enum('status', ['active', 'suspended', 'closed'])->default('active');
            $table->json('settings')->nullable(); // Wallet-specific settings
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'status']);
            $table->index(['status']);
            $table->index(['last_transaction_at']);
            
            // Foreign key constraints
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};

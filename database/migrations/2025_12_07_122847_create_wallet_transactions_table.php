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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 50)->unique(); // Format: WLT-XXXXXXXXXXXX
            $table->unsignedBigInteger('wallet_id');
            $table->enum('type', ['credit', 'debit', 'reserve', 'release', 'refund']);
            $table->enum('category', [
                'deposit', 'withdrawal', 'payment', 'refund', 'bonus', 
                'penalty', 'swap_fee', 'maintenance', 'transfer', 'battery_checkout'
            ])->default('payment');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('description');
            $table->string('reference_type')->nullable(); // Model class name (polymorphic)
            $table->unsignedBigInteger('reference_id')->nullable(); // Related model ID
            $table->json('metadata')->nullable(); // Additional transaction data
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['wallet_id', 'type']);
            $table->index(['wallet_id', 'category']);
            $table->index(['wallet_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['transaction_id']);
            $table->index(['created_at']);
            
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};

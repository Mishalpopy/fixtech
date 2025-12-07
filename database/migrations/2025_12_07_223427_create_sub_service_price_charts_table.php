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
        Schema::create('sub_service_price_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_service_id')->constrained('sub_services')->onDelete('cascade');
            $table->string('time_duration'); // e.g., "First 1 Hr", "Upto 1.5 Hrs"
            $table->decimal('current_price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable(); // For struck-through price
            $table->boolean('is_urgent')->default(false);
            $table->integer('order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_service_price_charts');
    }
};

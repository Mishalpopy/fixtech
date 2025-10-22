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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('passenger_name');
            $table->string('email');
            $table->string('passenger_number');
            $table->string('agent_number');
            $table->string('pickup_address');
            $table->integer('adults');
            $table->integer('childrens');
            $table->double('adult_price', 15, 2);
            $table->double('child_price', 15, 2);
            $table->date('flight_date');
            $table->time('pickup_time');
            $table->string('total_amount');
            $table->string('status');
            $table->double('deposit', 15, 2);
            $table->string('voucher_code');
            $table->string('redemption_code');
            $table->longText('comments');
            $table->string('booking_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

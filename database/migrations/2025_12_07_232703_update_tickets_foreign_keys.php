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
        Schema::table('tickets', function (Blueprint $table) {
            // Add foreign key constraints for service_id and sub_service_id
            // These are added in a separate migration because services and sub_services
            // tables are created after the tickets table
            if (Schema::hasTable('services')) {
                $table->foreign('service_id')
                    ->references('id')
                    ->on('services')
                    ->onDelete('set null');
            }
            
            if (Schema::hasTable('sub_services')) {
                $table->foreign('sub_service_id')
                    ->references('id')
                    ->on('sub_services')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['sub_service_id']);
        });
    }
};

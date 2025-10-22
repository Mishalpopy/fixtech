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
            if (!Schema::hasColumn('tickets', 'assigned_partner_id')) {
                $table->foreignId('assigned_partner_id')->nullable()->constrained('partners')->onDelete('set null');
            }
            if (!Schema::hasColumn('tickets', 'assigned_by')) {
                $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('tickets', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable();
            }
            if (!Schema::hasColumn('tickets', 'partner_notes')) {
                $table->text('partner_notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['assigned_partner_id']);
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['assigned_partner_id', 'assigned_by', 'assigned_at', 'partner_notes']);
        });
    }
};

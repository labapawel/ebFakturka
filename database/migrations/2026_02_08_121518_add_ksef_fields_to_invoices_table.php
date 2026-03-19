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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'type')) {
                $table->string('type')->default('sales')->after('id');
            }
            if (!Schema::hasColumn('invoices', 'ksef_number')) {
                $table->string('ksef_number', 35)->nullable()->after('currency_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['type', 'ksef_number']);
        });
    }
};

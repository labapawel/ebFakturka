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
            if (!Schema::hasColumn('invoices', 'bank_account')) {
                $table->string('bank_account')->nullable()->after('currency_id');
            }
            if (!Schema::hasColumn('invoices', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('bank_account');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['bank_account', 'bank_name']);
        });
    }
};

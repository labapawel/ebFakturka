<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->boolean('is_jst')->default(false)->after('city');
            $table->boolean('is_vat_group_member')->default(false)->after('is_jst');
        });
    }

    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropColumn(['is_jst', 'is_vat_group_member']);
        });
    }
};

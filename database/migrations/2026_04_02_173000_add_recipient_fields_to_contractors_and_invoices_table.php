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
        Schema::table('contractors', function (Blueprint $table) {
            $table->string('recipient_name')->nullable()->after('city');
            $table->string('recipient_nip')->nullable()->after('recipient_name');
            $table->string('recipient_street')->nullable()->after('recipient_nip');
            $table->string('recipient_building')->nullable()->after('recipient_street');
            $table->string('recipient_apartment')->nullable()->after('recipient_building');
            $table->string('recipient_postal_code')->nullable()->after('recipient_apartment');
            $table->string('recipient_city')->nullable()->after('recipient_postal_code');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('buyer_recipient_name')->nullable()->after('buyer_city');
            $table->string('buyer_recipient_nip')->nullable()->after('buyer_recipient_name');
            $table->string('buyer_recipient_street')->nullable()->after('buyer_recipient_nip');
            $table->string('buyer_recipient_building')->nullable()->after('buyer_recipient_street');
            $table->string('buyer_recipient_apartment')->nullable()->after('buyer_recipient_building');
            $table->string('buyer_recipient_postal_code')->nullable()->after('buyer_recipient_apartment');
            $table->string('buyer_recipient_city')->nullable()->after('buyer_recipient_postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_name', 'recipient_nip', 'recipient_street', 'recipient_building',
                'recipient_apartment', 'recipient_postal_code', 'recipient_city'
            ]);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'buyer_recipient_name', 'buyer_recipient_nip', 'buyer_recipient_street', 'buyer_recipient_building',
                'buyer_recipient_apartment', 'buyer_recipient_postal_code', 'buyer_recipient_city'
            ]);
        });
    }
};

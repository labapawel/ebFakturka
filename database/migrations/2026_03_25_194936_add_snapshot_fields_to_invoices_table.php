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
            // Seller Data
            $table->string('seller_name')->nullable()->after('user_id');
            $table->string('seller_nip')->nullable()->after('seller_name');
            $table->string('seller_street')->nullable()->after('seller_nip');
            $table->string('seller_building')->nullable()->after('seller_street');
            $table->string('seller_apartment')->nullable()->after('seller_building');
            $table->string('seller_postal_code')->nullable()->after('seller_apartment');
            $table->string('seller_city')->nullable()->after('seller_postal_code');

            // Buyer Data
            $table->string('buyer_name')->nullable()->after('seller_city');
            $table->string('buyer_nip')->nullable()->after('buyer_name');
            $table->string('buyer_street')->nullable()->after('buyer_nip');
            $table->string('buyer_building')->nullable()->after('buyer_street');
            $table->string('buyer_apartment')->nullable()->after('buyer_building');
            $table->string('buyer_postal_code')->nullable()->after('buyer_apartment');
            $table->string('buyer_city')->nullable()->after('buyer_postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'seller_name', 'seller_nip', 'seller_street', 'seller_building', 'seller_apartment', 'seller_postal_code', 'seller_city',
                'buyer_name', 'buyer_nip', 'buyer_street', 'buyer_building', 'buyer_apartment', 'buyer_postal_code', 'buyer_city'
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->string('ksef_legal_name')->nullable()->after('city');
            $table->string('ksef_legal_street')->nullable()->after('ksef_legal_name');
            $table->string('ksef_legal_building')->nullable()->after('ksef_legal_street');
            $table->string('ksef_legal_apartment')->nullable()->after('ksef_legal_building');
            $table->string('ksef_legal_postal_code')->nullable()->after('ksef_legal_apartment');
            $table->string('ksef_legal_city')->nullable()->after('ksef_legal_postal_code');
            $table->string('ksef_correspondence_name')->nullable()->after('ksef_legal_city');
            $table->string('ksef_correspondence_street')->nullable()->after('ksef_correspondence_name');
            $table->string('ksef_correspondence_building')->nullable()->after('ksef_correspondence_street');
            $table->string('ksef_correspondence_apartment')->nullable()->after('ksef_correspondence_building');
            $table->string('ksef_correspondence_postal_code')->nullable()->after('ksef_correspondence_apartment');
            $table->string('ksef_correspondence_city')->nullable()->after('ksef_correspondence_postal_code');
            $table->string('ksef_customer_number')->nullable()->after('ksef_correspondence_city');
        });
    }

    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropColumn([
                'ksef_legal_name',
                'ksef_legal_street',
                'ksef_legal_building',
                'ksef_legal_apartment',
                'ksef_legal_postal_code',
                'ksef_legal_city',
                'ksef_correspondence_name',
                'ksef_correspondence_street',
                'ksef_correspondence_building',
                'ksef_correspondence_apartment',
                'ksef_correspondence_postal_code',
                'ksef_correspondence_city',
                'ksef_customer_number',
            ]);
        });
    }
};

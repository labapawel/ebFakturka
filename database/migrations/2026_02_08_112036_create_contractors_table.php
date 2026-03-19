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
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nip')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_building')->nullable();
            $table->string('address_apartment')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->json('gus_data')->nullable(); // Cache danych z GUS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};

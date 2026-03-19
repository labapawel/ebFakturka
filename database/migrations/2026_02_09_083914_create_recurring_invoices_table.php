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
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('frequency')->default('monthly'); // monthly, quarterly, yearly, custom
            $table->integer('frequency_interval')->default(1);
            $table->date('start_date');
            $table->date('next_issue_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->foreignId('currency_id')->constrained();
            $table->string('payment_method')->default('transfer');
            $table->decimal('net_total', 10, 2)->default(0);
            $table->decimal('vat_total', 10, 2)->default(0);
            $table->decimal('gross_total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoices');
    }
};

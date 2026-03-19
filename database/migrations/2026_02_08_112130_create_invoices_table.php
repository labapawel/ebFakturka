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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('issue_date');
            $table->date('sale_date');
            $table->date('due_date');
            $table->string('payment_method');
            $table->foreignId('contractor_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('currency_id')->constrained();
            $table->decimal('net_total', 10, 2);
            $table->decimal('vat_total', 10, 2);
            $table->decimal('gross_total', 10, 2);
            $table->string('status')->default('draft'); // draft, sent, paid, cancelled
            $table->string('ksef_number')->nullable();
            $table->string('ksef_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

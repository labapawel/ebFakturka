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
        Schema::create('recurring_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_invoice_id')->constrained('recurring_invoices')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('quantity', 10, 3);
            $table->string('unit');
            $table->decimal('net_price', 10, 2);
            $table->decimal('vat_rate', 5, 4); // np. 0.2300 for 23%
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('gross_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_items');
    }
};

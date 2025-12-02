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
        Schema::create('bill_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_bill');
            $table->foreign('id_bill')->references('id')->on('bills')->onDelete('cascade');
            $table->unsignedBigInteger('id_product')->nullable();
            $table->foreign('id_product')->references('id')->on('products');
            $table->string('code');
            $table->string('name');
            $table->decimal('quantity', 20, 2)->default(1.00);
            $table->decimal('price', 20, 2);
            $table->decimal('priceU', 20, 2);
            $table->decimal('total_amount', 20, 2);
            $table->decimal('discount_percent', 20, 2);
            $table->decimal('discount', 20, 2);
            $table->decimal('net_amount', 20, 2);
            $table->integer('iva');
            $table->string('price_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_details');
    }
};

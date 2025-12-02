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
        Schema::create('repayments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sucursal');
            $table->foreign('id_sucursal')->references('id')->on('sucursals')->onDelete('cascade');
            $table->unsignedBigInteger('id_bill');
            $table->foreign('id_bill')->references('id')->on('bills')->onDelete('cascade');
            $table->unsignedBigInteger('id_product');
            $table->foreign('id_product')->references('id')->on('products');
            $table->unsignedBigInteger('id_seller');
            $table->foreign('id_seller')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_client');
            $table->foreign('id_client')->references('id')->on('clients');
            $table->unsignedBigInteger('id_closure')->nullable();
            $table->foreign('id_closure')->references('id')->on('closures')->onDelete('cascade');
            $table->unsignedBigInteger('id_closureI')->nullable();
            $table->foreign('id_closureI')->references('id')->on('closures')->onDelete('cascade');
            $table->unsignedBigInteger('id_currency');
            $table->foreign('id_currency')->references('id')->on('currencies');
            $table->string('name_product'); 
            $table->decimal('rate', 20, 2);
            $table->decimal('rate_official', 20, 2);
            $table->string('abbr_repayment')->nullable(); 
            $table->string('abbr_official')->nullable(); 
            $table->string('abbr_principal')->nullable();
            $table->string('code');
            $table->decimal('quantity', 20, 2)->default(1.00);
            $table->decimal('amount', 20, 2);
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repayments');
    }
};

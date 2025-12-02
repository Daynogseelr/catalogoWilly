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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sucursal');
            $table->foreign('id_sucursal')->references('id')->on('sucursals')->onDelete('cascade');
            $table->unsignedBigInteger('id_product');
            $table->foreign('id_product')->references('id')->on('products');
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_shopping')->nullable(); 
            $table->foreign('id_shopping')->references('id')->on('shoppings')->onDelete('cascade');
            $table->unsignedBigInteger('id_bill')->nullable(); 
            $table->foreign('id_bill')->references('id')->on('bills')->onDelete('cascade');
            $table->unsignedBigInteger('id_repayment')->nullable(); 
            $table->foreign('id_repayment')->references('id')->on('repayments')->onDelete('cascade');
            $table->unsignedBigInteger('id_inventory_adjustment')->nullable(); 
            $table->foreign('id_inventory_adjustment')->references('id')->on('inventory_adjustments')->onDelete('cascade');
            $table->unsignedBigInteger('id_inventory_transfer')->nullable(); 
            $table->foreign('id_inventory_transfer')->references('id')->on('inventory_transfers')->onDelete('cascade');
            $table->decimal('cost',20,2)->nullable();
            $table->decimal('addition',20,4)->nullable(); 
            $table->decimal('subtraction',20,4)->nullable(); 
            $table->decimal('quantity',20,4);
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};

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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sucursal');
            $table->foreign('id_sucursal')->references('id')->on('sucursals')->onDelete('cascade');
            $table->unsignedBigInteger('id_seller')->nullable();
            $table->foreign('id_seller')->references('id')->on('users');
            $table->unsignedBigInteger('id_client');
            $table->foreign('id_client')->references('id')->on('clients');
            $table->unsignedBigInteger('id_currency_principal')->nullable(); 
            $table->foreign('id_currency_principal')->references('id')->on('currencies');
            $table->unsignedBigInteger('id_currency_official')->nullable();
            $table->foreign('id_currency_official')->references('id')->on('currencies');
            $table->unsignedBigInteger('id_currency_bill')->nullable(); 
            $table->foreign('id_currency_bill')->references('id')->on('currencies');
            $table->unsignedBigInteger('id_closure')->nullable();
            $table->foreign('id_closure')->references('id')->on('closures')->onDelete('cascade');
            $table->unsignedBigInteger('id_closureI')->nullable();
            $table->foreign('id_closureI')->references('id')->on('closures')->onDelete('cascade');
            $table->string('code')->nullable();
            $table->decimal('rate_bill',20,2)->nullable(); 
            $table->decimal('rate_official',20,2)->nullable(); 
            $table->string('abbr_bill')->nullable(); 
            $table->string('abbr_official')->nullable(); 
            $table->string('abbr_principal')->nullable();
            $table->decimal('discount_percent',20,2); 
            $table->decimal('total_amount',30,2);
            $table->decimal('discount',20,2);
            $table->decimal('net_amount',30,2);
            $table->string('type');
            $table->integer('status');
            $table->decimal('payment',20,2);
            $table->integer('creditDays')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};

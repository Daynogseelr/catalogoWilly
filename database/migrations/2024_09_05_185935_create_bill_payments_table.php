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
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sucursal');
            $table->foreign('id_sucursal')->references('id')->on('sucursals')->onDelete('cascade');
            $table->unsignedBigInteger('id_bill');
            $table->foreign('id_bill')->references('id')->on('bills')->onDelete('cascade');
            $table->unsignedBigInteger('id_seller');
            $table->foreign('id_seller')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_payment_method')->nullable();
            $table->foreign('id_payment_method')->references('id')->on('payment_methods')->onDelete('cascade');
            $table->unsignedBigInteger('id_closure')->nullable();
            $table->foreign('id_closure')->references('id')->on('closures')->onDelete('cascade');
            $table->unsignedBigInteger('id_closureI')->nullable();
            $table->foreign('id_closureI')->references('id')->on('closures')->onDelete('cascade');
            $table->string('code_repayment')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('amount', 20, 2);
            $table->decimal('rate', 20, 2);
            $table->string('collection');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
    }
};

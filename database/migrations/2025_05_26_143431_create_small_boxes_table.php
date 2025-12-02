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
        Schema::create('small_boxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_employee');
            $table->foreign('id_employee')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_closure')->nullable();
            $table->foreign('id_closure')->references('id')->on('closures')->onDelete('cascade');
            $table->unsignedBigInteger('id_closureIndividual')->nullable();
            $table->foreign('id_closureIndividual')->references('id')->on('closures')->onDelete('cascade');
            $table->unsignedBigInteger('id_currency')->nullable();
            $table->foreign('id_currency')->references('id')->on('currencies')->onDelete('cascade');
            $table->decimal('cash')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('small_boxes');
    }
};

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->string('code2')->nullable();
            $table->string('name');
            $table->integer('units');
            $table->string('name_detal')->nullable();
            $table->string('url')->nullable();
            $table->decimal('cost',20,2);
            $table->decimal('utility_detal',20,2)->nullable();
            $table->decimal('price_detal',20,2)->nullable();
            $table->decimal('utility',20,2);
            $table->decimal('price',20,2);
            $table->decimal('utility2',20,2)->nullable();
            $table->decimal('price2',20,2)->nullable();
            $table->decimal('utility3',20,2)->nullable();
            $table->decimal('price3',20,2)->nullable();
            $table->integer('stock_min')->nullable();
            $table->integer('iva');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

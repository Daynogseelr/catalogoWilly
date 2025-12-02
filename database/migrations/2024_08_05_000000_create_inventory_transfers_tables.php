<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTransfersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->unsignedBigInteger('id_sucursal_from');
            $table->unsignedBigInteger('id_sucursal_to');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=pendiente,1=aprobado,2=cancelado,3=cancelado');
            $table->timestamps();
        });

        Schema::create('inventory_transfer_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inventory_transfer_id');
            $table->foreign('inventory_transfer_id')->references('id')->on('inventory_transfers')->onDelete('cascade');
            $table->unsignedBigInteger('id_product');
            $table->foreign('id_product')->references('id')->on('products')->onDelete('cascade');
            $table->decimal('quantity', 14, 4)->default(0);
            $table->timestamps();

           
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_transfer_items');
        Schema::dropIfExists('inventory_transfers');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransferItem extends Model
{
    protected $table = 'inventory_transfer_items';
    protected $guarded = [];

    public function transfer()
    {
        return $this->belongsTo(InventoryTransfer::class, 'inventory_transfer_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'id_product');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransfer extends Model
{
    protected $table = 'inventory_transfers';
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(InventoryTransferItem::class, 'inventory_transfer_id');
    }

    public function fromSucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'id_sucursal_from');
    }

    public function toSucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'id_sucursal_to');
    }
}

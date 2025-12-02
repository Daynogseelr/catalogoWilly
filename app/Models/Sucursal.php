<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursals';

    protected $fillable = [
        'name',
        'rif',
        'state',
        'city',
        'postal_zone',
        'direction',
        'percent',
        'status',
    ];

    protected $casts = [
        'percent' => 'decimal:2',
        'status' => 'integer',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'sucursal_user', 'id_sucursal', 'id_user')->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SucursalUser extends Model
{
    // Nombre de la tabla pivot
    protected $table = 'sucursal_user';

    // Campos guardables
    protected $fillable = [
        'id_user',
        'id_sucursal',
    ];

}
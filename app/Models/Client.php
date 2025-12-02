<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'nationality',
        'ci',
        'phone',
        'email',
        'direction',
        'discount',
        'status'
    ];
    public function bills(){
        return $this->hasMany(Bill::class, 'id_client');
    }
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shopping extends Model
{

    use HasFactory;
    
    protected $fillable = ['id_sucursal','id_user','codeBill','name','date','total'];
    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmallBox extends Model
{
    use HasFactory;
    
    protected $fillable = ['id_employee','id_currency','id_closure','id_closureIndividual','id_currency','cash'];

    public function employee(){
        return $this->belongsTo(User::class, 'id_employee');
    }
    public function closure(){
        return $this->belongsTo(Closure::class, 'id_closure');
    }
    public function closureIndividual(){
        return $this->belongsTo(Closure::class, 'id_closureIndividual');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddCategory extends Model
{
    use HasFactory;
    public function category(){
        return $this->belongsTo(Category::class, 'id_category');
    }
    public function product(){
        return $this->belongsTo(Product::class, 'id_product');
    }
    protected $fillable = ['id_category','id_product'];
}

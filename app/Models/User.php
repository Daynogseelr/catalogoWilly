<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
   
    public function stocks(){
        return $this->hasMany(Stock::class, 'id_user');
    }
     public function sucursals()
    {
        return $this->belongsToMany(Sucursal::class, 'sucursal_user', 'id_user', 'id_sucursal')->withTimestamps();
    }
    public function seller()
    {
        return $this->hasMany(Bill::class, 'id_seller');
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name','nationality','ci','phone','state','city','postal_zone','user','direction','password','type','percent','smallBox','detal','price','price2','price3','status'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}

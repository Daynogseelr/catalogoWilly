<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInfo extends Model
{
    protected $table = 'company_infos';

    protected $fillable = [
        'logo',
        'name',
        'address',
        'rif',
        'description',
        'phone',
        'photo1',
        'photo2',
        'photo3',
        'socials',
        'shipping_methods',
        'payment_methods',
        'password',
    ];

    protected $casts = [
        'socials' => 'array',
    ];
}

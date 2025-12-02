<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void{
        DB::table('users')->insert([
            'name' => 'DYOSER GONZALEZ',
            'nationality' => 'V',
            'ci' => '2462228',
            'phone' => '04168835169',
            'type' => 'ADMINISTRADOR',
            'direction' => 'CARUPANO',
            'percent' => '100',
            'smallBox' => '1',
            'detal' => '1',
            'price' => '1',
            'price2' => '1',
            'price3' => '1',
            'user' => 'ADMIN',
            'password' => Hash::make('ADMIN123'),
            'status' => '1',
        ]);
    }
}

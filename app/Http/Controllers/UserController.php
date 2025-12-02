<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SellerPayment;
use Illuminate\Http\Request;

class UserController extends Controller{
    public function indexEmployee(){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'ADMINISTRATIVO') {  
            return view('users.employee');
        }   
        return redirect()->route('indexStore');
    }
    public function indexClient(){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR' ||  auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            return view('users.client');
        } else {
            return redirect()->route('indexStore');
        }   
    }
}

<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\AddCategory;
use App\Models\Product;
use App\Models\User;
use App\Models\Bill;
use App\Models\Shopping; // Add this line
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Agrega esta línea
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller{
     public function index(Request $request){
        // obtener sucursal seleccionada por query param `sucursal` (id o nombre)
        $sucursal = null;
        $sucursalIdentifier = $request->query('sucursal');
        // if no query param, try to read selected sucursal from session (set by middleware or barra)
        if (! $sucursalIdentifier) {
            $sucursalIdentifier = session('selected_sucursal');
        }
        if ($sucursalIdentifier) {
            if (is_numeric($sucursalIdentifier)) {
                $sucursal = DB::table('sucursals')->where('id', intval($sucursalIdentifier))->first();
            } else {
                $sucursal = DB::table('sucursals')
                    ->where('name', $sucursalIdentifier)
                    ->orWhere(DB::raw("LOWER(REPLACE(name,' ', '-'))"), strtolower(str_replace(' ', '-', $sucursalIdentifier)))
                    ->first();
            }
        }

        $countCategory = Category::where('status', 1)->count();
        $countProduct = Product::count();
        $countClient = User::where('status', 1)->where('type', 'CLIENTE')->count();

        // Contadores y totales filtrados por sucursal cuando corresponde
        $countBill = Bill::where('status','!=', 0)
            ->when($sucursal, function($q) use ($sucursal){
                return $q->where('id_sucursal', $sucursal->id);
            })->count();

        $totalBilling = Bill::when($sucursal, function($q) use ($sucursal){
                return $q->where('id_sucursal', $sucursal->id);
            })->sum('net_amount'); // Calculate total billing

        $totalPurchases = Shopping::when($sucursal, function($q) use ($sucursal){
                return $q->where('id_sucursal', $sucursal->id);
            })->sum('total'); // Calculate total purchases

        // Calculate total stock value for selected sucursal (or global if none)
        $totalStock = 0;
        $products = Product::all();
        foreach ($products as $product) {
            if ($sucursal) {
                $lastStock = $product->stocks()->where('id_sucursal', $sucursal->id)->latest()->first();
            } else {
                $lastStock = $product->stocks()->latest()->first();
            }
            $stockQty = $lastStock ? $lastStock->quantity : 0;
            $totalStock += $stockQty * $product->cost;
        }

        return view('dashboard', compact('countCategory','countProduct','countClient','countBill', 'totalBilling', 'totalPurchases', 'totalStock', 'sucursal'));
    }
     
}

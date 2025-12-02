<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    function indexStore(Request $request)
    {
        DB::statement("SET SQL_MODE=''");
        $categories = Category::select('id', 'name')
            ->where('status', 1)
            ->get();
        $products = Product::select('products.price as price', 'products.id as id', 'products.code as code', 'url', 'name', 'status')
            ->where('status', '!=', '0')
            ->orderByDesc('updated_at')
            ->paginate(42);
        // Obtener moneda principal
        $currencies = Currency::where('status', 1)->get();
        
        return view('stores.store', compact(
            'categories',
            'currencies',
        ));
    }
   public function indexStoreAjax(Request $request)
{
    DB::statement("SET SQL_MODE=''");

    $products = Product::select('products.price as price', 'products.id as id', 'products.code as code', 'url', 'name', 'status')
        ->where('status', '!=', '0');

    if ($request->category != '' && $request->category != 'TODAS') {
        $products->join('add_categories', 'add_categories.id_product', '=', 'products.id')
            ->where('add_categories.id_category', $request->category);
    }

    if ($request->scope != '') {
        $products->where('products.name', 'like', "%$request->scope%");
    }

    // Si es "all", no filtra por inventario ni por stock

    if ($request->has('sort_by') && in_array($request->sort_by, ['asc', 'desc', 'available', 'unavailable'])) {
        $products = $this->sortProductsByStock($products, $request->sort_by);
    } else {
        $products->orderByDesc('products.updated_at');
    }

    $products = $products->paginate(42);

    // Obtener percent de la sucursal seleccionada una sola vez
    $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
    $percent = 0;
    if ($sucursalId) {
        $percent = DB::table('sucursals')->where('id', $sucursalId)->value('percent') ?? 0;
    }

    foreach ($products as $product) {
        $latestStock = DB::table('stocks')
            ->where('id_product', $product->id)
            ->orderBy('id', 'desc')
            ->first();
        $product->stock = $latestStock ? $latestStock->quantity : 0;
        // Aplicar percent de sucursal al precio mostrado (precio principal)
        if (is_numeric($product->price) && $percent != 0) {
            $product->price = round($product->price + ($product->price * ($percent / 100)), 4);
        }
    }

    $currencies = Currency::where('status', 1)->get();
    $currencyPrincipal = Currency::where('is_principal', 1)->first();
    $currencyOfficial = Currency::where('is_official', 1)->first();
    $currencySelected = Currency::find($request->id_currencyStore);

    return response()->json(
        view('stores.products', compact(
            'products',
            'currencies',
            'currencyPrincipal',
            'currencyOfficial',
            'currencySelected',
        ))->render()
    );
}

    private function sortProductsByStock($products, $sortBy, $selectedInventoryId = null)
    {
        // Asegurarse de que la subconsulta de stock use el id_inventory seleccionado
        // Y que el ORDER BY sea robusto para casos donde no hay stock (NULL)
        $stockSubquery = '(SELECT quantity FROM stocks
                        WHERE id_product = products.id
                        ORDER BY created_at DESC LIMIT 1)';

        $products->addSelect(DB::raw($stockSubquery . ' as stock_value')); // Usar un alias diferente para evitar conflictos

        switch ($sortBy) {
            case 'asc':
                // Ordena los productos con stock 0 o NULL al final
                $products->orderByRaw("COALESCE(" . $stockSubquery . ", 0) ASC");
                break;
            case 'desc':
                // Ordena los productos con stock 0 o NULL al final
                $products->orderByRaw("COALESCE(" . $stockSubquery . ", 0) DESC");
                break;
            case 'available':
                $products->whereRaw($stockSubquery . ' > 0');
                break;
            case 'unavailable':
                $products->whereRaw($stockSubquery . ' = 0'); // Incluye 0 y NULL
                break;
        }

        return $products;
    }
    public function mostrarProduct(Request $request)
    {
        $product = Product::find($request->id);
        // Suma de los últimos stocks de cada inventario
        $latestStock = DB::table('stocks')
            ->where('id_product', $product->id)
            ->orderBy('id', 'desc')
            ->first();
        $product->stock = $latestStock ? $latestStock->quantity : 0;
        $currencyPrincipal = Currency::where('is_principal', 1)->first();
        $currencyOfficial = Currency::where('is_official', 1)->first();
        $currencySelected = Currency::find($request->id_currencyStore);

        return response()->json([
            'product' => $product,
            'currencyOfficial' => $currencyOfficial,
            'currencyPrincipal' => $currencyPrincipal,
            'currencySelected' => $currencySelected,
        ]);
    }
}

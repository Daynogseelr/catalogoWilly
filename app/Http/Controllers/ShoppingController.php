<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Shopping;
use App\Models\Stock;
use App\Models\Currency;
use App\DataTables\ShoppingDataTable;
use App\Models\Sucursal; 

class ShoppingController extends Controller
{
    public function index(ShoppingDataTable $dataTable)
    {
        return $dataTable->render('products.shopping');
    }
    public function indexAddShopping(Request $request)
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            $products = Product::where('status',1)->get();
            $categories = Category::where('status', '1')->get();
            // Monedas principales y secundarias con tasa de cambio activa
            $currencyPrincipal = Currency::where('is_principal', 1)->first();
            $currencies = Currency::where('status', 1)->get();
            return view('products.addShopping', compact(
                'products','categories',
                'currencyPrincipal','currencies'
            ));
        } else {
            return redirect()->route('indexStore');
        }
    }

    public function storeShopping(Request $request)
    {
        $request->validate([
            'codeBill' => 'required|string|max:100',
            'date' => 'required|date',
            'nameProvider' => 'required|string|max:200',
            'totalBill' => 'required|numeric',
            'productsTableData' => 'required|array',
            'currency_id' => 'required|exists:currencies,id',
        ]);
        // obtener sucursal seleccionada en sesión
        $sucursalId = session('selected_sucursal');
        if (empty($sucursalId) || ! Sucursal::find($sucursalId)) {
            return response()->json(['error' => 'Seleccione una sucursal válida antes de registrar la compra.'], 422);
        }

        // Crear nueva compra
        $shopping = new Shopping();  
        $shopping->id_sucursal = $sucursalId;
        $shopping->id_user = auth()->id();
        $shopping->codeBill = $request->codeBill;
        $shopping->date = $request->date;
        $shopping->name = $request->nameProvider;
        $shopping->total = $request->totalBill;
        $shopping->save();

        // Guardar productos y seriales
        foreach ($request->productsTableData as $productData) {
            $product = Product::find($productData['id']);
            if ($product) {
                $quantity = $productData['quantity'];
                if ($quantity > 0) {
                    $lastStock = Stock::where('id_product', $product->id)
                        ->latest()->first();
                    $newQuantity = $lastStock ? $lastStock->quantity + $quantity : $quantity;
                    $stock = new Stock();
                    $stock->id_sucursal = $sucursalId;
                    $stock->id_product = $product->id;
                    $stock->id_user = auth()->id();
                    $stock->id_shopping = $shopping->id;
                    $stock->cost =$productData['cost'];;
                    $stock->addition = $quantity;
                    $stock->subtraction = 0;
                    $stock->quantity = $newQuantity;
                    $stock->description = 'COMPRA FACTURA '. $request->codeBill;
                    $stock->save();
                }
                // Actualizar costo
                $product->cost = $productData['cost'];
                // Recalcular precios solo si tiene utilidad
                // Detal
                if ($product->utility_detal !== null && $product->utility_detal > 0) {
                    $product->price_detal = ($product->cost * (1 + ($product->utility_detal / 100))) / $product->units;
                }
                // Precio 1
                if ($product->utility !== null && $product->utility > 0) {
                    $product->price = $product->cost * (1 + ($product->utility / 100));
                }
                // Precio 2
                if ($product->utility2 !== null && $product->utility2 > 0) {
                    $product->price2 = $product->cost * (1 + ($product->utility2 / 100));
                }
                // Precio 3
                if ($product->utility3 !== null && $product->utility3 > 0) {
                    $product->price3 = $product->cost * (1 + ($product->utility3 / 100));
                }
                $product->save();
            }
        }
        return response()->json([
            'id' => $shopping->id,
            'message' => 'Compra registrada correctamente'
        ]);
    }
    public function codeProduct()
    {
        $product = Product::orderByDesc('code')
            ->latest()
            ->first();
        if ($product) {
            return Response()->json($product->code + 1);
        } else {
            return Response()->json(1);
        }
    }
    public function addProductShopping(Request $request)
    {
        $products = [];
        if ($request->id_product && is_array($request->id_product) && count($request->id_product) > 0) {
            foreach ($request->id_product as $id_product) {
                $product = Product::find($id_product);
                if ($product) { // Check if the product exists
                    $products[] = $product;
                }
            }
        }
        return response()->json(['products' => $products]);
    }
}
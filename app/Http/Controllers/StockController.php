<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Models\Category;
use App\Models\Shopping;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function indexStock($id)
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            $product = Product::find($id);
            return view('products.stock', compact('product'));
        } else {
            return redirect()->route('indexStore');
        }
    }

    public function indexShopping()
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            return view('products.shopping');
        } else {
            return redirect()->route('indexStore');
        }
    }
    public function indexAddShopping()
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            $products = Product::where('status', 1)->get();
            $categories = Category::where('status', '1')->get();
            return view('products.addShopping', compact('products', 'categories'));
        } else {
            return redirect()->route('indexStore');
        }
    }
    public function ajaxStock($id_product)
    {
        DB::statement("SET SQL_MODE=''");
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        $stocksQuery = DB::table('stocks')
            ->join('users', 'users.id', '=', 'stocks.id_user')
            ->leftJoin('products', 'products.id', '=', 'stocks.id_product')
            ->select(
                'stocks.addition',
                'stocks.subtraction',
                'stocks.quantity',
                'stocks.description',
                'stocks.created_at',
                'users.name',
                'users.nationality',
                'users.ci',
                'products.units',
                'stocks.id_sucursal' // opcional si quieres mostrar la sucursal
            )
            ->where('stocks.id_product', $id_product);

        // filtrar por sucursal si hay una seleccionada
        if ($sucursalId) {
            $stocksQuery->where('stocks.id_sucursal', $sucursalId);
        }

        $stocks = $stocksQuery->orderBy('stocks.id', 'desc')->get();

        if (request()->ajax()) {
            return datatables()->of($stocks)
                ->addColumn('detal', function ($row) {
                    $qty = isset($row->quantity) ? (float) $row->quantity : 0;
                    $units = isset($row->units) && $row->units > 0 ? (float) $row->units : 1;
                    return (int) round($qty * $units);
                })
                ->addIndexColumn()
                ->make(true);
        }
        return redirect()->route('indexStore');
    }


    public function ajaxShopping()
    {
        DB::statement("SET SQL_MODE=''");
        if (request()->ajax()) {
            return datatables()->of(Shopping::select('*')->where('status', 1))
                ->addColumn('action', 'products.shopping-action')
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make(true);
        }
        return redirect()->route('indexStore');
    }
    public function storeStock(Request $request)
    {
        $request->validate([
            'stocks'  => 'required',
            'descriptions'  => 'required',
        ]);
        $stock = Stock::where('id_product', $request->id_product)
            ->orderBy('id', 'desc') // <- usar id en lugar de fecha
            ->first();
        if ($request->status == 'Reponer') {
            if ($stock) {
                $quantity = $stock->quantity + $request->stocks;
            } else {
                $quantity = $request->stocks;
            }
            $stocknew   =   Stock::Create(
                [
                    'id_product' => $request->id_product,
                    'id_user' => auth()->id(),
                    'addition' => $request->stocks,
                    'subtraction' => 0,
                    'quantity' => $quantity,
                    'description' => $request->descriptions,
                ]
            );
        } else {
            if ($stock) {
                $quantity = $stock->quantity - $request->stocks;
            } else {
                $quantity = 0 - $request->stocks;
            }
            $stocknew   =   Stock::Create(
                [
                    'id_product' => $request->id_product,
                    'id_user' => auth()->id(),
                    'addition' => 0,
                    'subtraction' => $request->stocks,
                    'quantity' => $quantity,
                    'description' => $request->descriptions,
                ]
            );
        }

        return Response()->json($stocknew);
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
    public function storeShopping(Request $request)
    {
        // Validar los datos del formulario (opcional, pero recomendado)
        $request->validate([
            'codeBill' => 'required|string|max:100',
            'date' => 'required|date',
            'nameProvider' => 'required|string|max:200',
            'totalBill' => 'required|numeric',
            'productsTableData' => 'required|array',
        ]);
        // Crear nueva compra
        $shopping = new Shopping();
        $shopping->codeBill = $request->codeBill;
        $shopping->date = $request->date;
        $shopping->name = $request->nameProvider;
        $shopping->total = $request->totalBill;
        $shopping->save();
        // Guardar o actualizar productos de la tabla
        foreach ($request->productsTableData as $productData) {
            $product = Product::find($productData['id']);
            if ($product) {
                $quantity = $productData['quantity'];
                if ($quantity > 0) {
                    // Buscar el último registro de stock para el producto
                    $lastStock = Stock::where('id_product', $product->id)->orderBy('id', 'desc')->first();
                    if ($lastStock) {
                        // Actualizar el stock sumando la cantidad anterior
                        $newQuantity = $lastStock->quantity + $quantity;
                        $stock = new Stock();
                        $stock->id_product = $product->id;
                        $stock->id_user =  auth()->id();
                        $stock->addition = $quantity;
                        $stock->subtraction = 0;
                        $stock->quantity = $newQuantity;
                        $stock->description = 'COMPRA FACTURA ' . $request->codeBill;
                        $stock->save();
                    } else {
                        // Crear un nuevo registro de stock
                        $stock = new Stock();
                        $stock->id_product = $product->id;
                        $stock->id_user =  auth()->id();
                        $stock->addition = $quantity;
                        $stock->subtraction = 0;
                        $stock->quantity = $quantity;
                        $stock->description = 'COMPRA FACTURA ' . $request->codeBill;
                        $stock->save();
                    }
                }
                $product->cost = $productData['cost'];
                $product->utility = $productData['utility'];
                $product->price = $productData['price'];
                $product->save();
            }
        }
        return response()->json(['message' => 'Compra registrada correctamente']);
    }
}

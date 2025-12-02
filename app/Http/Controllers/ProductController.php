<?php

namespace App\Http\Controllers;

use App\DataTables\ProductDataTable;
use App\Models\Product;
use App\Models\User;
use App\Models\Stock;
use App\Models\AddCategory;
use App\Models\Currency;
use App\Models\Category;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function indexProduct(ProductDataTable $dataTable)
    {
        $products = Product::get();
        $categories = Category::where('status', 1)->get();
        // Trae todas las monedas y sus tasas de cambio (incluye la principal con tasa 1)
        $currencies = Currency::where('status', 1)->get();
        $currencyPrincipal = Currency::where('is_principal', 1)->first();
        // sucursal seleccionada en sesión (puede ser null)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        $sucursalPercent = 0;
        if ($sucursalId) {
            $sucursalPercent = DB::table('sucursals')->where('id', $sucursalId)->value('percent') ?? 0;
        }

        return $dataTable->render('products.product', compact('products', 'categories', 'currencies', 'currencyPrincipal', 'sucursalPercent'));
    }
    public function indexLabel()
    {
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR' ||  auth()->user()->type == 'ADMINISTRATIVO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'EMPLEADO') {
            // Pasar sucursales para el filtro
            return view('products.label');
        } else {
            return redirect()->route('indexStore');
        }
    }
    public function ajax(Request $request)
    {
        $currencyId = $request->get('currency_id');
        $stockFilter = $request->get('stock_filter', 'all');

        $query = Product::query();
        // Filtrar por tipo de stock
        if ($stockFilter == 'min') {
            $query->whereIn('id', function ($sub) {
                $sub->select('id_product')
                    ->from('stocks')
                    ->whereRaw('
                    quantity <= (
                        SELECT stock_min FROM products WHERE products.id = stocks.id_product
                    )
                ')
                    ->whereRaw('id = (
                    SELECT MAX(id) FROM stocks s2 WHERE s2.id_product = stocks.id_product 
                )');
            });
        } elseif ($stockFilter == 'max') {
            $query->whereIn('id', function ($sub) {
                $sub->select('id_product')
                    ->from('stocks')
                    ->whereRaw('
                    quantity > (
                        SELECT stock_min FROM products WHERE products.id = stocks.id_product
                    )
                ')
                    ->whereRaw('id = (
                    SELECT MAX(id) FROM stocks s2 WHERE s2.id_product = stocks.id_product 
                )');
            });
        }
        // Obtener tasa de cambio
        $currency = Currency::find($currencyId);
        $tasa = $currency->rate;

        return datatables()
            ->eloquent($query)
            ->addColumn('price_currency', function ($row) use ($tasa) {
                return number_format($row->price * $tasa, 2);
            })
            ->addColumn('stock', function ($row) {
                $stock = Stock::where('id_product', $row->id)
                    ->orderBy('id', 'desc')
                    ->first();
                return $stock ? $stock->quantity : 0;
            })
            ->addColumn('images', function ($row) {
                $url = $row->url;
                if ($url) {
                    return '<img src="' . asset('storage/' . $url) . '" style="width:40px;height:40px;border-radius:6px;margin-right:4px;" />';
                }
                return '';
            })
            ->addColumn('action', 'products.product-action')
            ->rawColumns(['images', 'action'])
            ->make(true);
    }
    public function ajaxLabel(Request $request)
    {
        DB::statement("SET SQL_MODE=''");
        // Obtener parámetros de filtro: sucursal y tipo de precio
        $sucursalId = $request->input('sucursal');
        $priceType = $request->input('price_type', 'price'); // valor: 'detal','price','price2','price3'

        $productsQuery = Product::query();

        // Aplicar filtro por disponibilidad en sucursal si se indicó
        if ($sucursalId) {
            // Solo incluir productos con stock > 0 en la sucursal seleccionada
            $productsQuery->whereIn('id', function ($q) use ($sucursalId) {
                $q->select('id_product')
                    ->from('stocks')
                    ->where('id_sucursal', $sucursalId)
                    ->where('quantity', '>', 0);
            });
        }

        // Excluir productos que no tengan precio según el tipo seleccionado
        if ($priceType === 'detal') {
            $productsQuery->whereNotNull('price_detal')->where('price_detal', '>', 0);
        } elseif ($priceType === 'price') {
            $productsQuery->whereNotNull('price')->where('price', '>', 0);
        } elseif ($priceType === 'price2') {
            $productsQuery->whereNotNull('price2')->where('price2', '>', 0);
        } elseif ($priceType === 'price3') {
            $productsQuery->whereNotNull('price3')->where('price3', '>', 0);
        }

        if ($request->ajax()) {
            return DataTables::eloquent($productsQuery)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->input('search.value') != '') {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('products.code', 'like', "%{$searchValue}%")
                                ->orWhere('products.code2', 'like', "%{$searchValue}%")
                                ->orWhere('products.name', 'like', "%{$searchValue}%")
                                ->orWhere('products.name_detal', 'like', "%{$searchValue}%");
                        });
                    }
                })
                ->addColumn('name', function ($product) use ($priceType) {
                    if ($priceType === 'detal' && !empty($product->name_detal)) {
                        return $product->name_detal;
                    }
                    return $product->name;
                })
                ->addColumn('stock', function ($product) use ($sucursalId, $priceType) {
                    // Construir la consulta para obtener el último stock del producto.
                    $latestStockQuery = DB::table('stocks')->where('id_product', $product->id);
                    if ($sucursalId) {
                        $latestStockQuery->where('id_sucursal', $sucursalId);
                    }
                    $latestStock = $latestStockQuery->orderBy('id', 'desc')->first();
                    $qty = $latestStock ? $latestStock->quantity : 0;
                    // Si el tipo de precio es 'detal', mostrar las unidades multiplicadas
                    if ($priceType === 'detal') {
                        $qtyToDisplay = floatval($qty) * floatval($product->units);
                    } else {
                        $qtyToDisplay = floatval($qty);
                    }
                    // Formatear para quitar ceros finales: 29.8000 -> 29.8, 19.0000 -> 19, 5.0700 -> 5.07
                    $formatted = rtrim(rtrim(sprintf('%.4f', $qtyToDisplay), '0'), '.');
                    // Si queda vacío (por ejemplo 0.0000 -> ''), devolver '0'
                    return $formatted === '' ? '0' : $formatted;
                })
                ->addColumn('url', function ($product) {
                    // Ajusta el path según tu almacenamiento
                    $img = $product->url
                        ? asset('storage/' . $product->url)
                        : asset('storage/products/product.jpg');
                    return '<img src="' . $img . '" alt="img" style="max-width:60px;max-height:60px;border-radius:6px;">';
                })
                ->addColumn('price', function ($product) use ($sucursalId, $priceType) {
                    // Determinar precio base según tipo
                    $basePrice = null;
                    if ($priceType === 'detal') $basePrice = $product->price_detal;
                    elseif ($priceType === 'price2') $basePrice = $product->price2;
                    elseif ($priceType === 'price3') $basePrice = $product->price3;
                    else $basePrice = $product->price;

                    if ($basePrice === null) return null;

                    // Obtener porcentaje de la sucursal (si aplica)
                    $percent = 0;
                    if ($sucursalId) {
                        $percent = DB::table('sucursals')->where('id', $sucursalId)->value('percent') ?? 0;
                    }

                    $adjusted = floatval($basePrice) * (1 + floatval($percent) / 100.0);
                    return number_format($adjusted, 2);
                })
                ->addIndexColumn()
                ->rawColumns(['url'])
                ->make(true);
        }

        return view('index');
    }
    public function storeProduct(Request $request)
    {
        try {
            $productId = $request->id;
            $rules = [
                'code' => [
                    'required',
                    Rule::unique('products')->ignore($request->id),
                    function ($attribute, $value, $fail) use ($request) {
                        if (Product::where('code2', $value)
                            ->where('id', '!=', $request->id)
                            ->exists()
                        ) {
                            $fail('EL codigo ya existe en codigo UPC.');
                        }
                    },
                    'different:code2',
                ],
                'code2' => [
                    'nullable',
                    Rule::unique('products')->ignore($request->id),
                    function ($attribute, $value, $fail) use ($request) {
                        if (Product::where('code', $value)
                            ->where('id', '!=', $request->id)
                            ->exists()
                        ) {
                            $fail('El codigo UPC ya existe en codigo.');
                        }
                    },
                    'different:code',
                ],
                'name' => [
                    'required',
                    Rule::unique('products')->ignore($request->id),
                ],
                'units' => 'required|integer|min:1',
                'name_detal' => 'nullable|string|max:200',
                'cost'  => 'required|numeric|min:0',
                'utility_detal' => 'nullable|numeric|min:0',
                'price_detal' => 'nullable|numeric|min:0',
                'utility'  => 'required|numeric|min:0',
                'price'  => 'required|numeric|min:0',
                'utility2' => 'nullable|numeric|min:0',
                'price2' => 'nullable|numeric|min:0',
                'utility3' => 'nullable|numeric|min:0',
                'price3' => 'nullable|numeric|min:0',
                'stock_min' => 'nullable|integer|min:0',
                'iva' => 'required|integer|in:0,1',
                'status' => 'nullable|integer',
                'url' => 'nullable',
                'modal_currency_id' => 'nullable|integer',
                'id_category' => 'nullable|array',
            ];

            // Validación condicional para name_detal
            if (($request->utility_detal && floatval($request->utility_detal) > 0)) {
                $rules['name_detal'] = 'required|string|max:200';
            }
            $request->validate($rules);
            $data = [
                'code' => $request->code,
                'code2' => $request->code2,
                'name' => $request->name,
                'units' => $request->units,
                'name_detal' => $request->name_detal,
                'cost' => $request->cost,
                'utility_detal' => $request->utility_detal,
                'price_detal' => $request->price_detal,
                'utility' => $request->utility,
                'price' => $request->price,
                'utility2' => $request->utility2,
                'price2' => $request->price2,
                'utility3' => $request->utility3,
                'price3' => $request->price3,
                'stock_min' => $request->stock_min,
                'iva' => $request->iva,
                'status' => $request->status ?? 1,
            ];
            if ($request->id) {
                $product = Product::find($request->id);
                if (!$product) abort(404);
                $product->update($data);
            } else {
                $product = Product::create($data);
            }
            if ($productId == NULL || $productId == '') {
                // Imagen
                if ($request->file('url')) {
                    $image = $request->file('url');
                    $path = $image->store('products', 'public');
                    $product->update(['url' => $path]);
                }

                // Categorías
                if ($request->id_category) {
                    AddCategory::where('id_product', $product->id)->delete();
                    foreach ($request->id_category as $id_category) {
                        AddCategory::create([
                            'id_category' => $id_category,
                            'id_product' => $product->id,
                        ]);
                    }
                }
            } else {
                if ($request->file('url')) {
                    $image = $request->file('url');
                    // Si ya existe una imagen en esta columna, la eliminamos del storage
                    if ($product->url && Storage::disk('public')->exists($product->url)) {
                        Storage::disk('public')->delete($product->url);
                    }
                    $path = $image->store('products', 'public');
                    $product->update(['url' => $path]);
                } else {
                    // Si no se subió una nueva imagen, mantenemos las URLs existentes
                    $product->update([
                        'url' => $product->url,
                    ]);
                }
                 if ($request->existencia != '' && $request->existencia != null) {
                    // sucursal seleccionada en sesión (puede ser null)
                    $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;

                    $latestStockQuery = DB::table('stocks')->where('id_product', $product->id);
                    if ($sucursalId) {
                        $latestStockQuery->where('id_sucursal', $sucursalId);
                    }
                    $latestStock = $latestStockQuery->orderBy('id', 'desc')->first();

                    if ($latestStock) {
                        if ($request->existencia != $latestStock->quantity) {
                            if ($request->existencia > $latestStock->quantity) {
                                $quantity = $request->existencia - $latestStock->quantity;
                                Stock::create([
                                    'id_product' => $product->id,
                                    'id_user' => auth()->id(),
                                    'id_sucursal' => $sucursalId,
                                    'addition' => $quantity,
                                    'subtraction' => 0,
                                    'quantity' => $request->existencia,
                                    'description' => 'Editar producto',
                                ]);
                            } else {
                                $quantity = $latestStock->quantity - $request->existencia;
                                Stock::create([
                                    'id_product' => $product->id,
                                    'id_user' => auth()->id(),
                                    'id_sucursal' => $sucursalId,
                                    'addition' => 0,
                                    'subtraction' => $quantity,
                                    'quantity' => $request->existencia,
                                    'description' => 'Editar producto',
                                ]);
                            }
                        }
                    } else {
                        Stock::create([
                            'id_product' => $product->id,
                            'id_user' => auth()->id(),
                            'id_sucursal' => $sucursalId,
                            'addition' => $request->existencia,
                            'subtraction' => 0,
                            'quantity' => $request->existencia,
                            'description' => 'Editar producto',
                        ]);
                    }
                }
                if ($request->id_category) {
                    AddCategory::where('id_product', $product->id)
                        ->delete();
                    foreach ($request->id_category as $id_category) {
                        AddCategory::Create([
                            'id_category' => $id_category,
                            'id_product' => $product->id,
                        ]);
                    }
                }
            }
            return Response()->json($product);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el producto',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    public function storeLabel(Request $request)
    {
        $id  = $request->id;
        $quantity = $request->quantity;
        return Response()->json(['id' => $id, 'quantity' => $quantity]);
    }

    /**
     * Find product by code or code2 and return its id.
     * Used by label view to generate a single ticket PDF.
     */
    public function findLabelProduct(Request $request)
    {
        $code = $request->code;
        if (!$code) {
            return response()->json(['message' => 'Código vacío'], 422);
        }
        $product = Product::where('code', $code)->orWhere('code2', $code)->first();
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json(['id' => $product->id]);
    }
    public function storeLabelAll(Request $request)
    {
        try {
            DB::statement("SET SQL_MODE=''");
            $code  = $request->code;
            // Consulta principal para obtener los stocks
            $stock = Stock::select('stocks.*', 'products.name as product_name', 'products.code as product_code') // Selecciona los campos necesarios
                ->join('products', 'stocks.id_product', '=', 'products.id') // Realiza el JOIN
                ->where('stocks.id_shopping', $code)
                ->get();
            if ($stock->isEmpty()) {
                return response()->json([
                    'status' => 'error', // Indica que es un error
                    'message' => 'No se encontraron registros de stock para este código y compañía.'
                ], 404);
            }
            $diff = 'no';
            foreach ($stock as $item) {
                $stockAnterior = Stock::where('id_product', $item->id_product)
                    ->where('id', '<', $item->id) // Stock con ID menor (anterior)
                    ->orderBy('id', 'desc') // Ordena descendente para obtener el más reciente
                    ->first();
                $diferencia = $item->quantity;
                if ($stockAnterior) {
                    $diferencia = $item->quantity - $stockAnterior->quantity;
                }
                if ($diferencia > 0) {
                    $diff = 'si';
                }
                if ($diff != 'no') {
                    break;
                }
            }
            if ($diff == 'no') {
                return response()->json([
                    'status' => 'error', // Indica que es un error
                    'message' => 'No hay stock positivos con este codigo.'
                ], 404);
            }
            return Response()->json(['code' => $code]);
        } catch (\Exception $e) {
            // Manejo de errores (importante para depuración)
            return response()->json(['message' => 'Error al obtener datos del stock: ' . $e->getMessage()], 500);
        }
    }
     public function editProduct(Request $request)
    {
        $where = ['id' => $request->id];
        $product  = Product::where($where)->first();
        $categories  = AddCategory::where('id_product', $product->id)->get();

        // sucursal seleccionada en sesión (puede ser null)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;

        // obtener último stock para la sucursal seleccionada (si existe), si no, último global
        $latestStockQuery = DB::table('stocks')->where('id_product', $product->id);
        if ($sucursalId) {
            $latestStockQuery->where('id_sucursal', $sucursalId);
        }
        $latestStock = $latestStockQuery->orderBy('id', 'desc')->first();

        return Response()->json([
            'product' => $product,
            'categories' => $categories,
            'quantity' => $latestStock,
        ]);
    }
    public function destroyProduct(Request $request)
    {
        $product = Product::where('id', $request->id)->delete();
        return Response()->json($product);
    }
    public function statusProduct(Request $request)
    {
        $product = Product::find($request->id);
        if ($product->status == '1') {
            $product->update(['status' => '0']);
        } else {
            $product->update(['status' => '1']);
        }
        return Response()->json($product);
    }
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'id' => 'required|exists:products,id', // Validate product ID
        ]);
        $base64Image = $request->input('image');
        $imageData = base64_decode($base64Image);
        if ($imageData === false) {
            return response()->json(['error' => 'Invalid base64 image data'], 400);
        }
        $product = Product::find($request->id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        // Delete the old image if it exists
        if ($product->url && Storage::disk('public')->exists($product->url)) {
            Storage::disk('public')->delete($product->url);
        }
        $filename = uniqid() . '.jpg';
        $newImagePath = 'products/' . $filename;
        // Store the new image
        $path = Storage::disk('public')->put($newImagePath, $imageData);

        if (!$path) {
            return response()->json(['error' => 'Failed to store new image'], 500);
        }
        // Update the product record with the new image path
        $product->update(['url' => $newImagePath]);
        return response()->json($product->id);
    }
    public function deleteProductImage(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
        ]);
        $product = Product::find($request->id);

        if ($product->url && Storage::disk('public')->exists($product->url)) {
            Storage::disk('public')->delete($product->url);
        }
        $product->url = null;
        $product->save();
        return response()->json(['success' => true]);
    }
}

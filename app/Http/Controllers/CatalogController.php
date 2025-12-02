<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bill;
use App\Models\Bill_detail;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CatalogController extends Controller
{
    // Mostrar catálogo público por sucursal y tipo de precio
    public function index($sucursalIdentifier, $priceType = 'price')
    {
        // soportar tanto id numérico como nombre/slug de sucursal
        if (is_numeric($sucursalIdentifier)) {
            $sucursal = DB::table('sucursals')->where('id', intval($sucursalIdentifier))->first();
        } else {
            // buscar por nombre exacto o por slug (minusculas, espacios a guiones)
            $sucursal = DB::table('sucursals')
                ->where('name', $sucursalIdentifier)
                ->orWhere(DB::raw("LOWER(REPLACE(name,' ','-'))"), strtolower(str_replace(' ', '-', $sucursalIdentifier)))
                ->first();
        }
        if (! $sucursal) {
            abort(404, 'Sucursal no encontrada');
        }

        $percent = floatval($sucursal->percent ?? 0);
        $products = Product::where('status', 1)->get();

        // Obtener último stock por producto para la sucursal (evitar N+1)
        $stockRows = DB::table('stocks as s')
            ->select('s.*')
            ->join(DB::raw('(SELECT id_product, MAX(id) as maxid FROM stocks WHERE id_sucursal = '.intval($sucursal->id).' GROUP BY id_product) as t'), function($join){
                $join->on('s.id', '=', 't.maxid');
            })
            ->get();
        $stockMap = [];
        foreach ($stockRows as $sr) {
            $stockMap[$sr->id_product] = $sr->quantity;
        }

        // Normalizar alias de price que vienen en la URL: 'mayorista' -> price2, 'mayorista2' -> price3
        $normalizedPrice = $priceType;
        if ($priceType === 'mayorista') $normalizedPrice = 'price2';
        if ($priceType === 'mayorista2') $normalizedPrice = 'price3';

        $items = $products->map(function ($product) use ($percent, $sucursal, $normalizedPrice, $priceType, $stockMap) {
            // Seleccionar precio según tipo; si no existe el precio específico, marcar como no disponible
            $hasPrice = true;
            switch ($normalizedPrice) {
                case 'detal':
                    $basePrice = $product->price_detal ?? null;
                    $name = $product->name_detal ?: $product->name;
                    if (is_null($basePrice) || $basePrice === '') $hasPrice = false;
                    break;
                case 'price2':
                    $basePrice = $product->price2 ?? null;
                    $name = $product->name;
                    if (is_null($basePrice) || $basePrice === '') $hasPrice = false;
                    break;
                case 'price3':
                    $basePrice = $product->price3 ?? null;
                    $name = $product->name;
                    if (is_null($basePrice) || $basePrice === '') $hasPrice = false;
                    break;
                default:
                    $basePrice = $product->price ?? null;
                    $name = $product->name;
                    if (is_null($basePrice) || $basePrice === '') $hasPrice = false;
                    break;
            }

            if (! $hasPrice) {
                return (object) ['id' => $product->id, 'hasPrice' => false];
            }

            // Aplicar percent de sucursal
            $price = $basePrice;
            if ($percent && $percent != 0) {
                $price = round($basePrice + ($basePrice * ($percent / 100)), 2);
            }

            // Último stock en la sucursal
            $quantity = isset($stockMap[$product->id]) ? floatval($stockMap[$product->id]) : 0;

            // Si el tipo de precio es 'detal', la existencia se expresa en unidades menores
            // Multiplicamos por el factor 'units' del producto y redondeamos hacia abajo
            if ($normalizedPrice === 'detal') {
                $units = intval($product->units ?? 1);
                $quantity = floor($quantity * max(1, $units));
            }

            return (object) [
                'id' => $product->id,
                'code' => $product->code ?? '',
                'name' => $name,
                'price' => $price,
                'basePrice' => $basePrice,
                'stock' => $quantity,
                'price_type' => $priceType,
                'image' => $product->url ?? null,
                'category_id' => $product->category_id ?? null,
                'description' => $product->description ?? null,
                'hasPrice' => true,
            ];
        })->filter(function($it){
            return isset($it->hasPrice) && $it->hasPrice === true;
        })->values();

        $company = DB::table('company_infos')->first();

        // categories y currencies para los filtros
        $categories = Category::where('status',1)->get();
        $currencies = Currency::orderByDesc('is_principal')->get();

        // usePrice2 flag (compatibilidad con la vista original)
        $usePrice2 = ($priceType === 'price2');

        return view('catalog.index', compact('items', 'sucursal', 'company', 'priceType', 'categories', 'currencies', 'products', 'usePrice2'));
    }

    // Generar PDF del catálogo según filtros (público)
    public function pdf(Request $request, $sucursalIdentifier, $priceType = 'price')
    {
        // reutilizar resolución de sucursal como en index
        if (is_numeric($sucursalIdentifier)) {
            $sucursal = DB::table('sucursals')->where('id', intval($sucursalIdentifier))->first();
        } else {
            $sucursal = DB::table('sucursals')
                ->where('name', $sucursalIdentifier)
                ->orWhere(DB::raw("LOWER(REPLACE(name,' ','-'))"), strtolower(str_replace(' ', '-', $sucursalIdentifier)))
                ->first();
        }
        if (! $sucursal) {
            abort(404, 'Sucursal no encontrada');
        }

        // filtros: category, q (search), currency
        $categoryId = $request->query('category');
        $q = $request->query('q');
        $currencyId = $request->query('currency');

        // Normalizar alias de price
        $normalizedPrice = $priceType;
        if ($priceType === 'mayorista') $normalizedPrice = 'price2';
        if ($priceType === 'mayorista2') $normalizedPrice = 'price3';

        $percent = floatval($sucursal->percent ?? 0);

        $query = Product::where('status', 1);
        if ($categoryId) $query->where('category_id', $categoryId);
        if ($q) $query->where(function($sub) use ($q) {
            $sub->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%");
        });

        $products = $query->orderBy('name')->get();

        // compute stock map similar to index for this sucursal
        $stockRows = DB::table('stocks as s')
            ->select('s.*')
            ->join(DB::raw('(SELECT id_product, MAX(id) as maxid FROM stocks WHERE id_sucursal = '.intval($sucursal->id).' GROUP BY id_product) as t'), function($join){
                $join->on('s.id', '=', 't.maxid');
            })
            ->get();
        $stockMap = [];
        foreach ($stockRows as $sr) {
            $stockMap[$sr->id_product] = $sr->quantity;
        }

        // currency
        $currency = Currency::find($currencyId) ?: Currency::where('is_principal',1)->first();
        $rate = $currency->rate ?? 1;

        $items = $products->map(function ($product) use ($percent, $sucursal, $normalizedPrice, $stockMap, $rate, $currency, $priceType) {
            $hasPrice = true;
            switch ($normalizedPrice) {
                case 'detal':
                    $basePrice = $product->price_detal ?? null;
                    $name = $product->name_detal ?: $product->name;
                    if (is_null($basePrice) || $basePrice === '') $hasPrice = false;
                    break;
                case 'price2':
                    $basePrice = $product->price2 ?? null;
                    $name = $product->name;
                    if (is_null($basePrice) || $basePrice === '') $hasPrice = false;
                    break;
                case 'price3':
                    $basePrice = $product->price3 ?? null;
                    $name = $product->name;
                    if (is_null($basePrice) || $basePrice === '') $hasPrice = false;
                    break;
                default:
                    $basePrice = $product->price ?? null;
                    $name = $product->name;
                    if (is_null($basePrice) || $basePrice === '') $hasPrice = false;
                    break;
            }

            if (! $hasPrice) {
                return (object) ['id' => $product->id, 'hasPrice' => false];
            }

            $price = $basePrice;
            if ($percent && $percent != 0) {
                $price = round($basePrice + ($basePrice * ($percent / 100)), 2);
            }
            $finalPrice = round($price * ($rate ?? 1), 2);
            $quantity = isset($stockMap[$product->id]) ? floatval($stockMap[$product->id]) : 0;
            if ($normalizedPrice === 'detal') {
                $units = intval($product->units ?? 1);
                $quantity = floor($quantity * max(1, $units));
            }

            // prepare local image path (prefer local file to avoid remote HTTP fetches)
            $candidate = $product->url ?? null;
            $localPath = $candidate ? public_path('storage/' . $candidate) : null;
            $defaultLocal = public_path('storage/products/product.jpg');
            // tiny 1x1 transparent PNG as ultimate fallback (data URI)
            $pixelData = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=';
            if ($localPath && file_exists($localPath) && is_readable($localPath)) {
                // use absolute filesystem path; Dompdf can read local files
                $imagePathForPdf = $localPath;
            } elseif (file_exists($defaultLocal) && is_readable($defaultLocal)) {
                $imagePathForPdf = $defaultLocal;
            } else {
                $imagePathForPdf = $pixelData;
            }

            return (object) [
                'id' => $product->id,
                'code' => $product->code ?? '',
                'name' => $name,
                'price' => $finalPrice,
                'basePrice' => $basePrice,
                'stock' => $quantity,
                'image' => $imagePathForPdf,
                'category' => $product->category?->name ?? '',
                'hasPrice' => true,
            ];
        })->filter(function($it){
            return isset($it->hasPrice) && $it->hasPrice === true;
        })->values();

        $company = DB::table('company_infos')->first();

        $view = view('catalog.pdf', [
            'items' => $items,
            'company' => $company,
            'sucursal' => $sucursal,
            'currency' => $currency,
            'priceType' => $priceType,
        ]);

        // Dompdf options: prefer local file reads (disable remote) and lower DPI for speed
        $options = [
            'isRemoteEnabled' => false,
            'dpi' => 72,
            'isHtml5ParserEnabled' => true,
        ];
        $pdf = Pdf::setOptions($options)->loadHTML($view->render())->setPaper('a4', 'portrait');
        // Stream the PDF so the browser displays it inline (open in new tab) instead of forcing download
        return $pdf->stream('catalogo_' . ($sucursal->name ?? 'catalogo') . '.pdf');
    }

    // Crear pedido (factura tipo PEDIDO) y retornar info para enviar WhatsApp
    public function storeOrder(Request $request, $sucursalIdentifier)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|numeric|min:0.01',
            'name' => 'required|string|max:200',
            'nationality' => 'required|string|max:5',
            'ci' => 'required|string|max:50',
        ]);

        // resolver sucursal por id o por nombre/slug (igual que en index)
        if (is_numeric($sucursalIdentifier)) {
            $sucursal = DB::table('sucursals')->where('id', intval($sucursalIdentifier))->first();
        } else {
            $sucursal = DB::table('sucursals')
                ->where('name', $sucursalIdentifier)
                ->orWhere(DB::raw("LOWER(REPLACE(name,' ','-'))"), strtolower(str_replace(' ', '-', $sucursalIdentifier)))
                ->first();
        }
        if (! $sucursal) return response()->json(['error' => 'Sucursal no encontrada'], 404);

        // Crear la factura como PEDIDO
        $total = 0;
        // Crear o buscar cliente temporal según CI
        $client = Client::firstOrCreate(['ci' => $request->ci], [
            'name' => $request->name,
            'nationality' => $request->nationality,
            'ci' => $request->ci,
            'status' => 1,
        ]);

        // Generar código secuencial para PEDIDO, similar a PRESUPUESTO pero empezando con 'O'
        $bills = Bill::where('type', 'PEDIDO')
            ->orderByDesc(DB::raw("CAST(SUBSTRING(code, 2) AS UNSIGNED)"))
            ->first();
        if ($bills) {
            $numberPart = intval(substr($bills->code, 1));
            $codeNew = 'O' . ($numberPart + 1);
        } else {
            $codeNew = 'O1';
        }

        $bill = Bill::create([
            'id_sucursal' => $sucursal->id,
            'id_seller' => null,
            'id_client' => $client->id,
            'code' => $codeNew,
            'discount_percent' => 0,
            'total_amount' => 0,
            'discount' => 0,
            'net_amount' => 0,
            'type' => 'PEDIDO',
            'status' => 0,
            'payment' => 0,
        ]);

        foreach ($data['items'] as $it) {
            $product = Product::find($it['id']);
            if (! $product) continue;

            // Seleccionar precio base según el campo enviado o por defecto
            $price_type = $it['price_type'] ?? 'price';
            // aceptar alias enviados desde la UI
            if ($price_type === 'mayorista') $price_type = 'price2';
            if ($price_type === 'mayorista2') $price_type = 'price3';
            switch ($price_type) {
                case 'detal':
                    $basePrice = $product->price_detal ?? $product->price;
                    $name = $product->name_detal ?: $product->name;
                    break;
                case 'price2':
                    $basePrice = $product->price2 ?? $product->price;
                    $name = $product->name;
                    break;
                case 'price3':
                    $basePrice = $product->price3 ?? $product->price;
                    $name = $product->name;
                    break;
                default:
                    $basePrice = $product->price;
                    $name = $product->name;
                    break;
            }

            $percent = floatval($sucursal->percent ?? 0);
            $price = $basePrice;
            if ($percent && $percent != 0) {
                $price = round($basePrice + ($basePrice * ($percent / 100)), 2);
            }

            $quantity = floatval($it['qty']);
            $total_amount = round($price * $quantity, 2);
            Bill_detail::create([
                'id_bill' => $bill->id,
                'id_product' => $product->id,
                'code' => $product->code ?? '',
                'name' => $name,
                'price' => $price,
                'priceU' => $price,
                'quantity' => $quantity,
                'total_amount' => $total_amount,
                'discount_percent' => 0,
                'discount' => 0,
                'net_amount' => $total_amount,
                'iva' => $product->iva ?? 0,
                'price_type' => $price_type,
            ]);

            $total += $total_amount;
        }

        // Actualizar totales
        $bill->total_amount = $total;
        $bill->net_amount = $total;
        $bill->save();

        // Totales adicionales: convertir según la moneda seleccionada y la moneda principal
        $principalCurrency = Currency::where('is_principal', 1)->first();
        $officialCurrency = Currency::where('is_official', 1)->first();

        $total_selected = $total * ($officialCurrency->rate ?? 1);
        $total_principal = $total * ($principalCurrency->rate ?? 1);

        $company = DB::table('company_infos')->first();
        return response()->json([
            'success' => true,
            'bill' => [
                'id' => $bill->id,
                'code' => $bill->code,
                'total' => $total,
            ],
            'totals' => [
                'selected' => round($total_selected, 2),
                'selected_abbr' => $officialCurrency->abbreviation ?? null,
                'principal' => round($total_principal, 2),
                'principal_abbr' => $principalCurrency->abbreviation ?? null,
            ],
            'company_phone' => $company->phone ?? null,
            'company_name' => $company->name ?? null,
        ]);
    }

    // Buscar cliente por CI (para autocompletar en el formulario público)
    public function clientLookup(Request $request, $id_sucursal)
    {
        $ci = $request->input('ci');
        if (! $ci) return response()->json(['found' => false]);

        $client = Client::where('ci', $ci)->first();
        if ($client) {
            return response()->json([
                'found' => true,
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'nationality' => $client->nationality,
                    'ci' => $client->ci,
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }

    // Unlock access to restricted price types for guest users by validating company password
    public function unlockPrice(Request $request, $sucursalIdentifier)
    {
        $data = $request->validate([
            'price_type' => 'required|string',
            'password' => 'required|string'
        ]);

        // Allow also our public aliases (mayorista / mayorista2)
        if (! in_array($data['price_type'], ['price2', 'price3', 'detal', 'price', 'mayorista', 'mayorista2'])) {
            return response()->json(['success' => false, 'message' => 'Tipo de precio no permitido'], 400);
        }

        $companyPassword = DB::table('company_infos')->value('password');

        if (! $companyPassword) {
            return response()->json(['success' => false, 'message' => 'No hay contraseña configurada'], 400);
        }

        if ($data['password'] === $companyPassword) {
            // Guardar en sesión que este tipo de precio está desbloqueado
            $key = "catalog_unlocked.{$data['price_type']}";
            session([$key => true]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Contraseña incorrecta'], 403);
    }
}

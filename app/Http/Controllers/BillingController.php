<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\Bill;
use App\Models\Bill_detail;
use App\Models\Bill_payment;
use App\Models\Client;
use App\Models\Repayment;
use App\Models\PaymentMethod;
use App\Models\SmallBox;
use App\Models\Stock;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Importa la clase Log
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use PhpParser\Node\Stmt\TryCatch;
use Yajra\DataTables\Facades\DataTables;


class BillingController extends Controller
{

    /**
     * Obtener el percent (decimal) de la sucursal seleccionada.
     * Devuelve 0 si no hay sucursal o no está definido.
     */
    private function getSucursalPercent($sucursalId)
    {
        if (!$sucursalId) return 0;
        $sucursal = DB::table('sucursals')->select('percent')->where('id', $sucursalId)->first();
        if (!$sucursal || $sucursal->percent === null) return 0;
        return floatval($sucursal->percent);
    }

    /**
     * Aplica el percent de sucursal al precio: price + price * percent/100
     */
    private function applySucursalPercent($price, $percent)
    {
        if (!$price) return $price;
        if (!$percent || $percent == 0) return $price;
        return round($price + ($price * ($percent / 100)), 4);
    }
    public function indexBilling()
    {
        if (auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            $employeeData = User::select('smallBox')->where('id', auth()->id())->first(); // Obtener smallBox también
            $employeeSmallBoxEnabled = $employeeData->smallBox ?? 0;
        } else {
            $employeeSmallBoxEnabled = 0;
        }
        $openSmallBoxModal = false; // Variable para controlar si se abre el modal
        if ($employeeSmallBoxEnabled == 1) {
            // Buscar si existe una caja chica abierta para este empleado
            $smallBox = SmallBox::where('id_employee', auth()->id())
                ->whereNull('id_closure') // Que no esté cerrada (id_closure es NULL)
                ->first();

            if (!$smallBox) {
                // Si no hay una caja chica abierta, entonces se debe abrir el modal
                $openSmallBoxModal = true;
            }
        }
        $clients = Client::select('*')->where('status', 1)->get();
        $paymentMethods = PaymentMethod::with('currency')->where('status', 1)->get();
        $budget = session('budget');
        if (!$budget) {
            $bill = Bill::with('bill_details')->where('id_seller', auth()->id())
                ->where('status', 0)->first();
            if ($bill) {
                if ($bill->bill_details && $bill->bill_details->isNotEmpty()) { // Check if not null and not empty
                    foreach ($bill->bill_details as $bill_detail) {
                        $bill_detail->delete();
                    }
                }
                $bill->delete();
            }
        }
        $id_shopper = session('id_shopper');
        $currencies = Currency::where('status', 1)->get();
        $currencyOfficial = Currency::where('is_official', 1)->first();
        $currencyPrincipal = Currency::where('is_principal', 1)->first();

        if ($id_shopper) {
            return view('billing.billing', compact('clients', 'id_shopper', 'paymentMethods', 'openSmallBoxModal', 'currencies', 'currencyPrincipal', 'currencyOfficial'));
        } else {
            return view('billing.billing', compact('clients', 'paymentMethods', 'openSmallBoxModal', 'currencies', 'currencyPrincipal', 'currencyOfficial'));
        }
    }
    public function ajaxBilling(Request $request)
    {
        DB::statement("SET SQL_MODE=''");
        Log::info("--- INICIO AJAX PRODUCT ---");
        Log::info("Request Data: " . json_encode($request->all()));
        try {
            // sucursal seleccionada en sesión (null = todas)
            $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
            Log::info('Sucursal seleccionada en sesión: ' . ($sucursalId ?? 'null'));
            // No filtrar por status aquí: seleccionar todos los productos
            $productsQuery = Product::query();
            // Subconsulta para obtener el id (MAX) del último stock por id_product, opcionalmente filtrada por sucursal
            $productsQuery->leftJoinSub(
                function ($query) use ($sucursalId) {
                    $query->from('stocks')
                        ->select('id_product', DB::raw('MAX(id) as latest_id'))
                        ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
                        ->groupBy('id_product');
                },
                'latest_stocks',
                function ($join) {
                    $join->on('products.id', '=', 'latest_stocks.id_product');
                }
            );

            // LEFT JOIN para obtener la cantidad real del último stock (que ya viene filtrado por sucursal si aplica)
            $productsQuery->leftJoin('stocks as actual_stock', function ($join) {
                $join->on('products.id', '=', 'actual_stock.id_product')
                    ->on('latest_stocks.latest_id', '=', 'actual_stock.id');
            });

            $productsQuery->select('products.*', DB::raw('COALESCE(actual_stock.quantity, 0) as stock_orderable'));

            $dataTables = DataTables::of($productsQuery)
                ->addColumn('stock', function ($product) {
                    return $product->stock_orderable ?? 'S/S';
                })
                ->addColumn('action', 'billing.billing-action')
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make(true);

            Log::info("--- FIN AJAX PRODUCT (ÉXITO) ---");
            return $dataTables;
        } catch (\Exception $e) {
            Log::error("Error en ajaxProduct: " . $e->getMessage());
            Log::error("Stack Trace: " . $e->getTraceAsString());

            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function ajaxBillWait(Request $request)
    {
        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;

        $query = DB::table('bills')
            ->join('clients as clients', 'clients.id', '=', 'bills.id_client')
            ->join('users as sellers', 'sellers.id', '=', 'bills.id_seller')
            ->leftJoin('bill_payments', 'bill_payments.id_bill', '=', 'bills.id')
            ->select(
                DB::raw('FORMAT(bills.net_amount, 2) as total'),
                'bills.id as id',
                'bills.type as type',
                'bills.payment as payment',
                'bills.created_at as created_at',
                'clients.name as clientName',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'sellers.name as sellerName',
            )
            ->where('bills.status', 2)
            ->where('bills.id_sucursal', $sucursalId)
            ->groupBy(
                'bills.id',
                'bills.type',
                'bills.payment',
                'bills.created_at',
                'bills.net_amount',  // También las que formateas
                'clients.name',
                'clients.nationality',
                'clients.ci',
                'sellers.name',
            );
        return DataTables::of($query->get())
            ->addColumn('action', 'billing.billWait-action')
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
    }
    public function storeShopper(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|min:2|max:500|string',
                'nationality' => 'required',
                'ci' => 'required|numeric|min:100000|max:9999999999|unique:clients,ci',
                'phone' => 'nullable|numeric|min:1000000000|max:99999999999',
                'email' => 'nullable|min:5|max:100',
                'direction' => 'nullable|min:1|max:250',
            ]);
            $client = Client::create([
                'name' => $request->name,
                'nationality' => $request->nationality,
                'ci' => $request->ci,
                'phone' => $request->phone, // Use the null coalescing operator
                'email' => $request->email,
                'status' => '1',
                'type' => 'CLIENTE',
                'direction' => $request->direction,
            ]);
            $id_shopper = $client->id;
            // Store the ID in the session as flash data
            session()->flash('id_shopper', $id_shopper);
            // Redirect to the indexBilling route
            return redirect()->route('indexBilling');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            return redirect()->back()->withErrors($e->validator)->withInput(); // Important: withInput() preserves the form data
        } catch (\Exception $e) {
            // Other errors (e.g., database errors)
            session()->flash('error', 'An error occurred while creating the shopper. Please try again. ' . $e->getMessage()); // Optionally show the error message for debugging (remove in production)
            return redirect()->back()->withInput(); // Preserve the form data
        }
    }
    public function mostrarBill(Request $request)
    {
        $bill = Bill::select('*')
            ->where('id_seller', auth()->id())
            ->where('status', 0)
            ->first();
        if (!$bill) {
            return Response()->json(['success' => 'error']);
        }
        $bill_details = Bill_detail::where('id_bill', $bill->id)
            ->get()
            ->map(function ($detail) {
                $product = Product::find($detail->id_product);
                return [
                    'id' => $detail->id,
                    'id_product' => $detail->id_product,
                    'code' => $detail->code,
                    'name' => $detail->name,
                    'price' => $detail->price,
                    'quantity' => $detail->quantity,
                    'discount_percent' => $detail->discount_percent,
                    'product_name' => $product->name ?? 'N/A',
                    'price_type' => $detail->price_type,
                ];
            });

        return Response()->json([
            'bill_details' => $bill_details,
            'success' => $bill_details->isEmpty() ? 'error' : 'bien'
        ]);
    }
    public function addBill(Request $request)
    {
        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        // Buscar por id_product o por code/code2
        if ($request->filled('id_product')) {
            $product = Product::find($request->id_product);
        } elseif ($request->filled('code')) {
            $product = Product::where(function ($query) use ($request) {
                $query->where('code', $request->code)
                    ->orWhere('code2', $request->code);
            })->first();
        } else {
            return response()->json(['error' => 'No se proporcionó producto'], 400);
        }

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        if (!$bill) {
            $bill = Bill::create([
                'id_sucursal' => $sucursalId,
                'id_seller' => auth()->id(),
                'id_client' => $request->id_client,
                'code' => 0,
                'discount_percent' => 0,
                'total_amount' => 0,
                'discount' => 0,
                'net_amount' => 0,
                'type' => 'FACTURA',
                'status' => 0,
                'payment' => 0,
            ]);
        }

        // Selecciona el precio y el nombre según el tipo
        switch ($request->price_type) {
            case 'detal':
                $price = $product->price_detal;
                $name = $product->name_detal ?: $product->name;
                break;
            case 'price2':
                $price = $product->price2;
                $name = $product->name;
                break;
            case 'price3':
                $price = $product->price3;
                $name = $product->name;
                break;
            default:
                $price = $product->price;
                $name = $product->name;
                break;
        }

        // Aplicar percent de sucursal al precio
        $percent = $this->getSucursalPercent($sucursalId);
        $price = $this->applySucursalPercent($price, $percent);

        $code = $product->code;

        // Antes de agregar, validamos stock disponible para evitar una llamada extra
        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;

        $stock = Stock::where('id_product', $product->id)
            ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
            ->orderByDesc('id')
            ->first();
        $availableStock = $stock ? $stock->quantity : 0;

        // Por defecto, la cantidad a verificar es 1
        $quantityToAdd = 1;
        // Si es detal, la cantidad a verificar es 1/unidades
        if ($request->price_type === 'detal') {
            $quantityToAdd = 1 / ($product->units > 0 ? $product->units : 1);
        }

        // Resta lo que ya está agregado en la factura vigente para este producto/tipo
        if ($bill) {
            $existingDetail = Bill_detail::where('id_product', $product->id)
                ->where('id_bill', $bill->id)
                ->where('price_type', $request->price_type)
                ->first();
            $alreadyAdded = $existingDetail ? $existingDetail->quantity : 0;
            if ($request->price_type === 'detal') {
                $alreadyAdded = $existingDetail ? ($existingDetail->quantity / ($product->units > 0 ? $product->units : 1)) : 0;
            }
            $availableStock -= $alreadyAdded;
        }

        if ($availableStock < $quantityToAdd) {
            return Response()->json(['res' => 'mal']);
        }

        // Busca el detalle por id_bill, id_product, price_type
        $bill_detail = Bill_detail::where('id_bill', $bill->id)
            ->where('id_product', $product->id)
            ->where('price_type', $request->price_type)
            ->first();

        if ($bill_detail) {
            $discount = $price * ($bill_detail->discount_percent / 100);
            $net_amount = $price - $discount;
            $bill_detail->quantity = $bill_detail->quantity + 1;
            $bill_detail->total_amount = $bill_detail->total_amount + $price;
            $bill_detail->discount = $bill_detail->discount + $discount;
            $bill_detail->net_amount = $bill_detail->net_amount + $net_amount;
            $bill_detail->save();
        } else {
            $discountpor = Client::where('id', $request->id_client)->first();
            if ($discountpor && $discountpor->discount != null && $discountpor->discount > 0) {
                $bill->discount_percent = $discountpor->discount;
                $bill->save();
                $discount = $price * ($discountpor->discount / 100);
                $net_amount = $price - $discount;
            } else {
                $discount = 0;
                $net_amount = $price;
            }
            $bill_detail = Bill_detail::create([
                'id_bill' => $bill->id,
                'id_product' => $product->id,
                'code' => $code,
                'name' => $name,
                'price' => $price,
                'priceU' => $price,
                'quantity' => 1,
                'total_amount' => $price,
                'discount_percent' => $bill->discount_percent,
                'discount' => $discount,
                'net_amount' => $net_amount,
                'iva' => $product->iva,
                'price_type' => $request->price_type,
            ]);
        }

        // Retornar el detalle agregado/actualizado para que el cliente lo renderice sin
        // requerir otra llamada (evita la ronda extra a mostrarBill)
        $detailToReturn = [
            'id' => $bill_detail->id,
            'id_product' => $bill_detail->id_product,
            'code' => $bill_detail->code,
            'name' => $bill_detail->name,
            'price' => $bill_detail->price,
            'quantity' => $bill_detail->quantity,
            'discount_percent' => $bill_detail->discount_percent,
            'price_type' => $bill_detail->price_type,
            'total_amount' => $bill_detail->total_amount,
            'discount' => $bill_detail->discount,
            'net_amount' => $bill_detail->net_amount,
        ];

        return Response()->json(['res' => 'bien', 'bill_detail' => $detailToReturn]);
    }

    public function deleteBillDetail(Request $request)
    {
        $bill_detail = Bill_detail::where('id', $request->id)->delete();
        return Response()->json($bill_detail);
    }
    public function deleteBill()
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())->where('status', 0)->first();
        Bill_detail::where('id_bill', $bill->id)->delete();
        $bill->delete();
        return Response()->json('ELIMINADOS');
    }
    public function changeClient(Request $request)
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        if ($bill) {

            $discountpor = Client::where('id', $request->id_client)->first();
            $bill_details  =  Bill_detail::select('*')->where('id_bill', $bill->id)->get();
            if ($discountpor->discount != null && $discountpor->discount > 0) {
                $bill->update([
                    'id_client' => $request->id_client,
                    'discount_percent' => $discountpor->discount,
                ]);
                $discount = 0;
                $net_amount = 0;
                if ($bill_details) {
                    foreach ($bill_details as $bill_detail) {
                        $discount = $bill_detail->total_amount * ($discountpor->discount / 100);
                        $net_amount = $bill_detail->total_amount - $discount;
                        $bill_detail->discount_percent =  $discountpor->discount;
                        $bill_detail->discount = $discount;
                        $bill_detail->net_amount = $net_amount;
                        $bill_detail->save();
                    }
                }
            } else {
                $bill->update([
                    'id_client' => $request->id_client,
                    'discount_percent' => 0,
                ]);
                if ($bill_details) {
                    foreach ($bill_details as $bill_detail) {
                        $bill_detail->discount_percent = 0;
                        $bill_detail->discount = 0;
                        $bill_detail->net_amount = $bill_detail->total_amount;
                        $bill_detail->save();
                    }
                }
            }
            return Response()->json('bien');
        } else {
            return Response()->json(null);
        }
    }
    public function changeClientVerify(Request $request)
    {
        $user = User::select('status')->find($request->id_client);

        return Response()->json($user->status);
    }
    public function updateQuantity(Request $request)
    {
        $bill_detail = Bill_detail::find($request->id);
        $total_amount = $bill_detail->price * $request->quantity;
        $discount = 0;
        if ($bill_detail->discount_percent != 0) {
            $discount = $total_amount * ($bill_detail->discount_percent / 100);
            $net_amount = $total_amount - $discount;
        } else {
            $net_amount = $total_amount;
        }
        $bill_detail->quantity = $request->quantity;
        $bill_detail->total_amount = $total_amount;
        $bill_detail->discount = $discount;
        $bill_detail->net_amount = $net_amount;
        $bill_detail->save();
        return Response()->json($bill_detail);
    }
    public function updateDiscount(Request $request)
    {
        $bill_detail = Bill_detail::find($request->id);
        $discount = 0;
        $bill_detail->discount_percent = $request->discount;
        $bill_detail->save();
        if ($bill_detail->discount_percent != 0) {
            $discount = $bill_detail->total_amount * ($bill_detail->discount_percent / 100);
            $net_amount = $bill_detail->total_amount - $discount;
        } else {
            $net_amount = $bill_detail->total_amount;
        }
        $bill_detail->discount = $discount;
        $bill_detail->net_amount = $net_amount;
        $bill_detail->save();
        return Response()->json($bill_detail);
    }
    public function verificaDiscount(Request $request)
    {
        if (auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            $employee = User::select('percent')->where('id', auth()->id())->first();
            if ($request->discount <= $employee->percent) {
                return Response()->json(['res' => 'bien', 'discount' => $request->discount, 'id' => $request->id]);
            } else {
                return Response()->json(['res' => 'mal', 'discount' => $employee->percent, 'id' => $request->id]);
            }
        } else {
            return Response()->json(['res' => 'bien', 'discount' => $request->discount, 'id' => $request->id]);
        }
    }
    public function authorizeDiscount(Request $request)
    {
        try {
            $employee = User::select('percent')->where('id_employee', auth()->id())->first();
            return Response()->json(['res' => 'mal', 'discount' => $employee->percent]);;
        } catch (QueryException $e) {
            // Manejo de errores de base de datos
            Log::error("Error de base de datos en autorización: " . $e->getMessage());
            return response()->json(['res' => 'mal', 'message' => 'Error en la base de datos. Por favor, contacte al administrador.', 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            // Manejo de otros errores
            Log::error("Error en autorización: " . $e->getMessage());
            return response()->json(['res' => 'mal', 'message' => 'Ocurrió un error inesperado. Por favor, contacte al administrador.', 'error' => $e->getMessage()]);
        }
    }
    /**
     * Validate supervisor password for the selected sucursal.
     * Returns JSON { ok: true } if any supervisor for the sucursal matches the password.
     */
    public function validateSupervisor(Request $request)
    {
        $password = $request->input('password');
        $sel = session('selected_sucursal');
        if (! $sel) {
            return response()->json(['ok' => false, 'message' => 'No hay sucursal seleccionada'], 422);
        }
        // Resolve sucursal id (accept numeric id or slug/name)
        $sucursalId = is_numeric($sel) ? intval($sel) : DB::table('sucursals')
            ->where('name', $sel)
            ->orWhere(DB::raw("LOWER(REPLACE(name,' ','-'))"), strtolower(str_replace(' ', '-', $sel)))
            ->value('id');
        if (! $sucursalId) {
            return response()->json(['ok' => false, 'message' => 'Sucursal no encontrada'], 404);
        }

        $supervisors = DB::table('sucursal_user')
            ->join('users', 'users.id', '=', 'sucursal_user.id_user')
            ->where('sucursal_user.id_sucursal', $sucursalId)
            ->where('users.type', 'SUPERVISOR')
            ->select('users.id', 'users.name', 'users.password')
            ->get();

        foreach ($supervisors as $sup) {
            if (Hash::check($password, $sup->password)) {
                return response()->json(['ok' => true, 'supervisor' => $sup->name], 200);
            }
        }

        return response()->json(['ok' => false], 401);
    }
    public function facturar(Request $request)
    {
        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        $bill_detailsProducts = Bill_detail::selectRaw('SUM(net_amount) as net_amount')
            ->where('id_product', '!=', NULL)
            ->where('id_bill', '=', $bill->id)
            ->first();
        $bill_detailsServices = Bill_detail::selectRaw('SUM(net_amount) as net_amount')
            ->where('id_product', NULL)
            ->where('id_bill', '=', $bill->id)
            ->first();
        $repayments = Repayment::select(
            DB::raw('FORMAT(SUM(amount), 2) as amount'),
            'code'
        )
            ->where('id_client', $request->id_client)
            ->where('status', 0)
            ->groupBy('code')
            ->get();
        if ($repayments->isEmpty()) {
            $res = 'notCredit';
        } else {
            $res = 'credit';
        }
        return Response()->json(['repayments' => $repayments, 'res' => $res, 'amountProduct' => round($bill_detailsProducts->net_amount, 2), 'amountService' => round($bill_detailsServices->net_amount, 2)]);
    }
    public function storeBill(Request $request)
    {
        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        try {
            $bill = Bill::select('*')->where('id_seller', auth()->id())
                ->where('status', 0)->first();
            $bills = Bill::where('type', '!=', 'PRESUPUESTO')
                ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
                ->get()
                ->map(function ($bill) {
                    $bill->code = intval(preg_replace('/[^0-9]/', '', $bill->code));
                    return $bill;
                })
                ->max('code');
            if ($bills == NULL) {
                $codeNew = 1;
            } else {
                $codeNew = $bills + 1;
            }
            foreach ($request->pagos as $pago) {
                if (strpos($pago['metodoId'], 'nota_credito_') === 0) {
                    // Es una nota de crédito
                    $code = str_replace('nota_credito_', '', $pago['metodoId']);

                    // Guarda el pago UNA SOLA VEZ con code_repayment
                    Bill_payment::create([
                        'id_sucursal' => $sucursalId,
                        'id_bill' => $bill->id,
                        'id_seller' => auth()->id(),
                        'code_repayment' => $code, // Nuevo campo para el código de la nota de crédito
                        'reference' => $pago['referencia'] ?? null,
                        'amount' => $pago['montoPrincipal'], // Monto en moneda principal
                        'rate' => isset($pago['rate']) ? $pago['rate'] : 1,
                        'collection' => 'CONTADO',
                    ]);

                    // Actualiza todos los repayments con ese código a status 1
                    Repayment::where('code', $code)->where('status', 0)->update(['status' => 1]);
                } else {
                    // Pago normal
                    Bill_payment::create([
                        'id_sucursal' => $sucursalId,
                        'id_bill' => $bill->id,
                        'id_seller' => auth()->id(),
                        'id_payment_method' => $pago['metodoId'],
                        'reference' => $pago['referencia'] ?? null,
                        'amount' => $pago['montoPrincipal'],
                        'rate' => isset($pago['rate']) ? $pago['rate'] : 1,
                        'collection' => 'CONTADO',
                    ]);
                }
            }
            $bill_detailes = Bill_detail::select('id', 'id_product', 'quantity', 'price', 'priceU', 'discount_percent', 'discount', 'net_amount', 'total_amount', 'price_type')
                ->where('id_bill', '=', $bill->id)
                ->get();
            $currencyBill = Currency::find($request->id_currency);
            foreach ($bill_detailes as $bill_detail) {
                $discount_percent = $bill_detail->discount_percent / 100;
                $discount = $bill_detail->price * $discount_percent;
                $bill_detail->priceU = $bill_detail->price - $discount;
                $bill_detail->total_amount = $bill_detail->price * $bill_detail->quantity;
                $bill_detail->discount = $bill_detail->total_amount * $discount_percent; // Calculate discount based on total_amount
                $bill_detail->net_amount = $bill_detail->total_amount - $bill_detail->discount;
                $bill_detail->save();
                // --- DESCONTAR STOCK ---
                $product = Product::find($bill_detail->id_product);
                if ($product) {
                    if ($bill_detail->price_type === 'detal') {
                        $quantityToDiscount = $bill_detail->quantity / ($product->units > 0 ? $product->units : 1);
                    } else {
                        $quantityToDiscount = $bill_detail->quantity;
                    }
                    $newStock = Stock::where('id_product', $product->id)
                        ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
                        ->orderByDesc('id')
                        ->first();
                    $newQuantity = ($newStock ? $newStock->quantity : 0) - $quantityToDiscount;
                    if ($newQuantity < 0) {
                        $newQuantity = 0; // Evitar stock negativo
                    }
                    // Actualizar el stock actual
                    // Registrar el movimiento de stock (resta)
                    Stock::create([
                        'id_product' => $product->id,
                        'id_user' => auth()->id(),
                        'id_bill' => $bill->id,
                        'id_sucursal' => $sucursalId,
                        'addition' => 0,
                        'subtraction' => $quantityToDiscount,
                        'quantity' => $newQuantity,
                        'description' => 'Venta factura ' . $codeNew,
                    ]);
                }
            }
            $bill_details = Bill_detail::selectRaw('SUM(total_amount) as total_amount,SUM(discount) as discount,SUM(net_amount) as net_amount')
                ->where('id_bill', '=', $bill->id)
                ->first();
            $currencyOfficial = Currency::where('is_official', 1)->first();
            $currencyPrincipal = Currency::where('is_principal', 1)->first();

            $bill->id_currency_principal = $currencyPrincipal->id;
            $bill->id_currency_official = $currencyOfficial->id;
            $bill->id_currency_bill = $request->id_currency;
            $bill->rate_bill = $currencyBill->rate;
            $bill->rate_official = $currencyOfficial->rate;
            $bill->abbr_bill = $currencyBill->abbreviation;
            $bill->abbr_official = $currencyOfficial->abbreviation;
            $bill->abbr_principal = $currencyPrincipal->abbreviation;
            $bill->total_amount = $bill_details->total_amount;
            $bill->discount = $bill_details->discount;
            $bill->net_amount = $bill_details->net_amount;
            $bill->code = $codeNew;
            $bill->status = 1;
            $bill->payment = 0;
            $bill->save();

            return Response()->json($bill->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }
    }

    public function storeBudget(Request $request)
    {
        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;

        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        $bills = Bill::where('type', 'PRESUPUESTO')
            ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
            ->orderByDesc(DB::raw('CAST(SUBSTRING(code, 2) AS UNSIGNED)'))
            ->first();
        if ($bills) {
            $numberPart = intval(substr($bills->code, 1));
            $codeNew = 'P' . ($numberPart + 1);
        } else {
            $codeNew = 'P1';
        }
        $bill_detailes = Bill_detail::select('id', 'id_product', 'quantity', 'price', 'priceU', 'discount_percent', 'discount', 'net_amount', 'total_amount')
            ->where('id_bill', '=', $bill->id)
            ->get();
        $currencyBill = Currency::find($request->id_currency);
        foreach ($bill_detailes as $bill_detail) {
            $discount_percent = $bill_detail->discount_percent / 100;
            $discount = $bill_detail->price * $discount_percent;
            $bill_detail->priceU = $bill_detail->price - $discount;
            $bill_detail->total_amount = $bill_detail->price * $bill_detail->quantity;
            $bill_detail->discount = $bill_detail->total_amount * $discount_percent; // Calculate discount based on total_amount
            $bill_detail->net_amount = $bill_detail->total_amount - $bill_detail->discount;
            $bill_detail->save();
        }
        $bill_details = Bill_detail::selectRaw('SUM(total_amount) as total_amount,SUM(discount) as discount,SUM(net_amount) as net_amount')
            ->where('id_bill', '=', $bill->id)
            ->first();
        $currencyOfficial = Currency::where('is_official', 1)->first();
        $currencyPrincipal = Currency::where('is_principal', 1)->first();

        $bill->id_currency_principal = $currencyPrincipal->id;
        $bill->id_currency_official = $currencyOfficial->id;
        $bill->id_currency_bill = $request->id_currency;
        $bill->rate_bill = $currencyBill->rate;
        $bill->rate_official = $currencyOfficial->rate;
        $bill->abbr_bill = $currencyBill->abbreviation;
        $bill->abbr_official = $currencyOfficial->abbreviation;
        $bill->abbr_principal = $currencyPrincipal->abbreviation;
        $bill->total_amount = $bill_details->total_amount;
        $bill->discount = $bill_details->discount;
        $bill->net_amount = $bill_details->net_amount;
        $bill->code = $codeNew;
        $bill->type = 'PRESUPUESTO';
        $bill->status = 1;
        $bill->payment = 0;
        $bill->save();
        return Response()->json($bill->id);
    }
    public function storeCredit(Request $request)
    {
        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        $bills = Bill::where('type', '!=', 'PRESUPUESTO')
            ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
            ->get()
            ->map(function ($bill) {
                $bill->code = intval(preg_replace('/[^0-9]/', '', $bill->code));
                return $bill;
            })
            ->max('code');
        if ($bills == NULL) {
            $codeNew = 1;
        } else {
            $codeNew = $bills + 1;
        }
        $bill_detailes = Bill_detail::select('id', 'id_product', 'quantity', 'price', 'priceU', 'discount_percent', 'discount', 'net_amount', 'total_amount')
            ->where('id_bill', '=', $bill->id)
            ->get();
        $currencyBill = Currency::find($request->id_currency);
        foreach ($bill_detailes as $bill_detail) {
            $discount_percent = $bill_detail->discount_percent / 100;
            $discount = $bill_detail->price * $discount_percent;
            $bill_detail->priceU = $bill_detail->price - $discount;
            $bill_detail->total_amount = $bill_detail->price * $bill_detail->quantity;
            $bill_detail->discount = $bill_detail->total_amount * $discount_percent; // Calculate discount based on total_amount
            $bill_detail->net_amount = $bill_detail->total_amount - $bill_detail->discount;
            $bill_detail->save();
            // --- DESCONTAR STOCK ---
            $product = Product::find($bill_detail->id_product);
            if ($product) {
                if ($bill_detail->price_type === 'detal') {
                    $quantityToDiscount = $bill_detail->quantity / ($product->units > 0 ? $product->units : 1);
                } else {
                    $quantityToDiscount = $bill_detail->quantity;
                }
                $newStock = Stock::where('id_product', $product->id)
                    ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
                    ->orderByDesc('id')
                    ->first();
                $newQuantity = ($newStock ? $newStock->quantity : 0) - $quantityToDiscount;
                if ($newQuantity < 0) {
                    $newQuantity = 0; // Evitar stock negativo
                }
                // Actualizar el stock actual
                // Registrar el movimiento de stock (resta)
                Stock::create([
                    'id_product' => $product->id,
                    'id_user' => auth()->id(),
                    'id_bill' => $bill->id,
                    'id_sucursal' => $sucursalId,
                    'addition' => 0,
                    'subtraction' => $quantityToDiscount,
                    'quantity' => $newQuantity,
                    'description' => 'Venta factura ' . $codeNew,
                ]);
            }
        }
        $bill_details = Bill_detail::selectRaw('SUM(total_amount) as total_amount,SUM(discount) as discount,SUM(net_amount) as net_amount')
            ->where('id_bill', '=', $bill->id)
            ->first();
        $currencyOfficial = Currency::where('is_official', 1)->first();
        $currencyPrincipal = Currency::where('is_principal', 1)->first();

        $bill->id_currency_principal = $currencyPrincipal->id;
        $bill->id_currency_official = $currencyOfficial->id;
        $bill->id_currency_bill = $request->id_currency;
        $bill->rate_bill = $currencyBill->rate;
        $bill->rate_official = $currencyOfficial->rate;
        $bill->abbr_bill = $currencyBill->abbreviation;
        $bill->abbr_official = $currencyOfficial->abbreviation;
        $bill->abbr_principal = $currencyPrincipal->abbreviation;
        $bill->total_amount = $bill_details->total_amount;
        $bill->discount = $bill_details->discount;
        $bill->net_amount = $bill_details->net_amount;
        $bill->code = $codeNew;
        $bill->type = 'CREDITO';
        $bill->creditDays = $request->creditDays;
        $bill->status = 1;
        $bill->payment = $bill_details->net_amount;
        $bill->save();
        return Response()->json($bill->id);
    }

    public function budget(Request $request)
    {

        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;

        // Buscar presupuesto por código, filtrando por sucursal si aplica
        $billOld = Bill::select('*')
            ->where('code', $request->code)
            ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
            ->first();
        if ($billOld) {
            $bill = Bill::select('id')->where('id_seller', auth()->id())
                ->where('status', 0)->first();
            if ($bill) {
                $bill_details = Bill_detail::where('id_bill', '=', $bill->id)->delete();
                $bill->id_client = $billOld->id_client;
                $bill->save();
            } else {
                if (!$bill) {
                    $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
                    $bill   =   Bill::create(
                        [
                            'id_sucursal' => $sucursalId,
                            'id_seller' => auth()->id(),
                            'id_client' => $billOld->id_client,
                            'code' => 0,
                            'discount_percent' => 0,
                            'total_amount' => 0,
                            'discount' => 0,
                            'net_amount' => 0,
                            'type' => 'FACTURA',
                            'status' => 0,
                            'payment' => 0,
                        ]
                    );
                }
            }
            $bill_detailsOld = Bill_detail::select('*')
                ->where('id_bill', '=', $billOld->id)
                ->get();
            foreach ($bill_detailsOld as $bill_detail) {
                $product = Product::find($bill_detail->id_product);
                if ($product) {
                    $price = $product->price;
                } else {
                    $price = $bill_detail->price;
                }
                $discount_percent = $bill_detail->discount_percent / 100;
                $discount = $price * $discount_percent;
                $bill_detail->priceU = $price - $discount;
                $bill_detail->total_amount = $price * $bill_detail->quantity;
                $bill_detail->discount = $bill_detail->total_amount * $discount_percent; // Calculate discount based on total_amount
                $bill_detail->net_amount = $bill_detail->total_amount - $bill_detail->discount;
                Bill_detail::create(
                    [
                        'id_bill' => $bill->id,
                        'id_product' => $bill_detail->id_product,
                        'code' => $bill_detail->code,
                        'name' => $bill_detail->name,
                        'price' => $price,
                        'priceU' => $bill_detail->priceU,
                        'quantity' => $bill_detail->quantity,
                        'total_amount' => $bill_detail->total_amount,
                        'discount_percent' => $bill_detail->discount_percent,
                        'discount' => $bill_detail->discount,
                        'net_amount' => $bill_detail->net_amount,
                        'iva' => $bill_detail->iva,
                        'price_type' => $bill_detail->price_type,
                    ]
                );
            }
            $id_shopper = $billOld->id_client;
        } else {
            $bill = Bill::select('id')->where('id_seller', auth()->id())
                ->where('status', 0)->first();
            if ($bill) {
                $bill_details = Bill_detail::where('id_bill', '=', $bill->id)->delete();
            }
            $id_shopper = 0;
        }
        session()->flash('id_shopper', $id_shopper);
        session()->flash('budget', 'budget');
        // Redirect to the indexBilling route
        return redirect()->route('indexBilling');
    }
    public function changeNoteCredit(Request $request)
    {
        $repayments = Repayment::select('*')->where('id_client', $request->id_client)->where('status', 0)->first();
        if ($repayments) {
            $res = 'credit';
        } else {
            $res = 'notCredit';
        }
        return Response()->json($res);
    }

    public function verifyStock(Request $request)
    {
        // Buscar por id_product o por code/code2
        if ($request->filled('id_product')) {
            $product = Product::find($request->id_product);
        } elseif ($request->filled('code')) {
            $product = Product::where(function ($query) use ($request) {
                $query->where('code', $request->code)
                    ->orWhere('code2', $request->code);
            })->first();
        } else {
            return response()->json(['res' => 'noproduct']);
        }

        if (!$product) {
            return response()->json(['res' => 'noproduct']);
        }

        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;

        $bill = Bill::where('id_seller', auth()->id())
            ->where('status', 0)
            ->first();
        $stock = Stock::where('id_product', $product->id)
            ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
            ->orderByDesc('id')
            ->first();
        $availableStock = $stock ? $stock->quantity : 0;
        // Por defecto, la cantidad a verificar es 1
        $quantityToCheck = 1;
        // Si es detal, la cantidad a verificar es 1/unidades
        if ($request->price_type === 'detal') {
            $quantityToCheck = 1 / ($product->units > 0 ? $product->units : 1);
        }
        if ($bill) {
            $bill_detail = Bill_detail::where('id_product', $product->id)
                ->where('id_bill', $bill->id)
                ->where('price_type', $request->price_type)
                ->first();
            $alreadyAdded = $bill_detail ? $bill_detail->quantity : 0;
            // Si es detal, la cantidad ya agregada también debe dividirse
            if ($request->price_type === 'detal') {
                $alreadyAdded = $bill_detail ? ($bill_detail->quantity / ($product->units > 0 ? $product->units : 1)) : 0;
            }
            $availableStock -= $alreadyAdded;
        }
        if ($availableStock >= $quantityToCheck) {
            return response()->json([
                'res' => 'bien',
                'id_product' => $product->id,
            ]);
        } else {
            return response()->json([
                'res' => 'mal',
            ]);
        }
    }

    public function verifyStockQuantity(Request $request)
    {
        $product = Product::find($request->id_product);
        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        $stock = Stock::where('id_product', $request->id_product)
            ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
            ->orderByDesc('id')
            ->first();

        // Por defecto, la cantidad a verificar es la recibida
        $quantityToCheck = $request->quantity;

        // Si es detal, divide la cantidad entre las unidades del producto
        if ($request->price_type === 'detal') {
            $quantityToCheck = $request->quantity / ($product->units > 0 ? $product->units : 1);
        }

        $available = $stock ? $stock->quantity : 0;
        if ($available >= $quantityToCheck) {
            return Response()->json(['res' => 'bien', 'id' => $request->id, 'quantity' => $request->quantity]);
        } else {
            $quantityMax = floor($available * ($request->price_type === 'detal' && $product->units > 0 ? $product->units : 1));
            return Response()->json(['res' => 'mal', 'id' => $request->id, 'quantity' => $request->quantity, 'quantityMax' => $quantityMax]);
        }
    }
    public function changeClientCredit(Request $request)
    {
        $billSUM = Bill::selectRaw('SUM(payment) as payment')
            ->where('id_client', $request->id_client)
            ->where('payment', '!=', 0)
            ->whereRaw('DATE_ADD(created_at, INTERVAL creditDays DAY) < NOW()')
            ->first();
        if ($billSUM->payment > 0) {
            return Response()->json(['res' => 'credit', 'billSUM' => $billSUM->payment]);
        } else {
            return Response()->json(['res' => 'nocredit',]);
        }
    }
    public function storeBillWait(Request $request)
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        $bill_details = Bill_detail::selectRaw('SUM(net_amount) as net_amount')
            ->where('id_bill', '=', $bill->id)
            ->first();
        $net_amount = $bill_details->net_amount;
        $bill->type = 'ESPERA';
        $bill->status = 2;
        $bill->net_amount = $net_amount;
        $bill->save();
        return Response()->json($bill->id);
    }
    public function billWaitStore(Request $request)
    {
        $bill = Bill::select('id')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        if ($bill) {
            $bill_details = Bill_detail::where('id_bill', '=', $bill->id)->delete();
            $bill = Bill::where('id_seller', auth()->id())
                ->where('status', 0)->delete();
        }
        $billWait = Bill::find($request->id_billWait);
        $billWait->id_seller = auth()->id();
        $billWait->net_amount = 0;
        $billWait->type = 'FACTURA';
        $billWait->status = 0;
        $billWait->save();
        session()->flash('id_shopper', $billWait->id_client);
        session()->flash('budget', 'budget');
        // Redirect to the indexBilling route
        return redirect()->route('indexBilling');
    }
    public function storeSmallBox(Request $request)
    {
        $request->validate([
            'small_boxes' => 'required|array|min:1',
            'small_boxes.*.id_currency' => 'required|exists:currencies,id',
            'small_boxes.*.cash' => 'required|numeric|min:0.01',
        ]);

        foreach ($request->small_boxes as $box) {
            SmallBox::create([
                'id_employee' => auth()->id(),
                'id_currency' => $box['id_currency'],
                'cash' => $box['cash'],
            ]);
        }

        return redirect()->route('indexBilling')->with('success2', 'Caja chica abierta con éxito.');
    }
    public function getProductPrices(Request $request)
    {
        if ($request->filled('id')) {
            $product = Product::find($request->id);
        } elseif ($request->filled('code')) {
            $product = Product::where(function ($query) use ($request) {
                $query->where('code', $request->code)
                    ->orWhere('code2', $request->code);
            })->first();
        } else {
            return response()->json(['error' => 'Debe enviar id o code'], 404);
        }

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

    $user = auth()->user();

    // Aplica porcentaje de la sucursal seleccionada a los precios
    $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
    $percent = $this->getSucursalPercent($sucursalId);

    $prices = [];
    if ($user->detal) $prices['detal'] = $this->applySucursalPercent($product->price_detal, $percent);
    if ($user->price) $prices['price'] = $this->applySucursalPercent($product->price, $percent);
    if ($user->price2) $prices['price2'] = $this->applySucursalPercent($product->price2, $percent);
    if ($user->price3) $prices['price3'] = $this->applySucursalPercent($product->price3, $percent);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'prices' => $prices
        ]);
    }
    public function mostrarProductBilling(Request $request)
    {
        $product = Product::find($request->id);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        // sucursal seleccionada en sesión (null = todas)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        // Obtener la existencia actual del producto desde la tabla de stock (filtrada por sucursal si aplica)
        $stock = Stock::where('id_product', $product->id)
            ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
            ->orderByDesc('id')
            ->first();
        $product->stock = $stock ? $stock->quantity : 0;

        // Aplicar percent de sucursal a precios
        $percent = $this->getSucursalPercent($sucursalId);

        return response()->json([
            'name' => $product->name,
            'existencia' => $product->stock ?? 0,
            'image_url' => $product->url ? asset('storage/' . $product->url) : asset('storage/products/product.jpg'),
            'prices' => [
                'detal' => $this->applySucursalPercent($product->price_detal, $percent),
                'price1' => $this->applySucursalPercent($product->price, $percent),
                'price2' => $this->applySucursalPercent($product->price2, $percent),
                'price3' => $this->applySucursalPercent($product->price3, $percent),
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\DataTables\ClientDataTable;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function indexClient(ClientDataTable $dataTable){
        if ( auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'ADMINISTRATIVO' || auth()->user()->type == 'EMPRESA') {
            return $dataTable->render('users.client');
        } 
        return redirect()->route('indexStore');
    }
    public function storeClient(Request $request)
    {
        $clientId = $request->id;
        if ($clientId == NULL || $clientId == '') {
            $request->validate([
                'name' => 'required|min:2|max:200|string',
                'nationality' => 'required',
                'ci' => 'required|numeric|min:100000|max:9999999999|unique:clients,ci',
                'discount' => 'nullable|numeric|min:0|max:100',
                'phone' => 'nullable|numeric|min:1000000000|max:99999999999',
                'email' => 'nullable|min:5|max:100',
                'direction' => 'nullable|min:1|max:250',
            ]);
        } else {
            $client = Client::find($request->id);
            if (!$client) {
                abort(404);
            }
            $request->validate([
                'name' => 'required|min:2|max:200|string',
                'nationality' => 'required',
                'ci' => 'required|numeric|min:100000|max:9999999999|unique:clients,ci,' . $client->id, // Correct unique rule
                'discount' => 'nullable|numeric|min:0|max:100',
                'phone' => 'nullable|numeric|min:1000000000|max:99999999999',
                'email' => 'nullable|min:5|max:100',
                'direction' => 'nullable|min:1|max:250',
            ]);
        }
        Client::updateOrCreate(
            [
                'id' => $clientId
            ],
            [
                'name' => $request->name,
                'nationality' => $request->nationality,
                'ci' => $request->ci,
                'phone' => $request->phone, // Use the null coalescing operator
                'email' => $request->email,
                'status' => '1',
                'direction' => $request->direction,
                'discount' => $request->discount ?? 0, // Default to 0 if not provided
            ]
        );
        return Response()->json($client);
    }
    public function editClient(Request $request)
    {
        $where = array('id' => $request->id);
        $client  = Client::where($where)->first();
        return Response()->json($client);
    }


    public function statusClient(Request $request)
    {
        $client = Client::find($request->id);
        if ($client->status == '1') {
            $client->update(['status' => '0']);
        } else {
            $client->update(['status' => '1']);
        }
        return Response()->json($client);
    }
}

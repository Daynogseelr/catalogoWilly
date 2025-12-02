<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyInfo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class CompanyInfoController extends Controller
{
    // Mostrar información pública
    public function show()
    {
        $info = CompanyInfo::first();
        return view('company_info.show', compact('info'));
    }

    // Formulario de edición (protegido por auth en rutas)
    public function edit()
    {
        $info = CompanyInfo::first();
        return view('company_info.edit', compact('info'));
    }

    // Guardar/actualizar información
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'rif' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'photo1' => 'nullable|image|max:2048',
            'photo2' => 'nullable|image|max:2048',
            'photo3' => 'nullable|image|max:2048',
            'socials' => 'nullable|array',
            'shipping_methods' => 'nullable|string',
            'payment_methods' => 'nullable|string',
            'password' => 'nullable|string|min:4',
        ]);
        $info = CompanyInfo::first();
        if (! $info) {
            $info = new CompanyInfo();
        }
        // Manejo de archivos: guarda en storage/app/public/company/
        $storeFile = function ($file, $oldPath = null) {
            if (! $file) return $oldPath;
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            return $file->store('company_info', 'public');
        };
        $info->name = $request->input('name');
        $info->address = $request->input('address');
        $info->rif = $request->input('rif');
        $info->description = $request->input('description');
        $info->phone = $request->input('phone');
        if ($request->hasFile('logo')) {
            $info->logo = $storeFile($request->file('logo'), $info->logo);
        }
        if ($request->hasFile('photo1')) {
            $info->photo1 = $storeFile($request->file('photo1'), $info->photo1);
        }
        if ($request->hasFile('photo2')) {
            $info->photo2 = $storeFile($request->file('photo2'), $info->photo2);
        }
        if ($request->hasFile('photo3')) {
            $info->photo3 = $storeFile($request->file('photo3'), $info->photo3);
        }
        // Socials: recibe como array desde el form
        $socials = $request->input('socials', []);
        // limpiar entradas vacías
        $socials = array_filter($socials, function ($v) { return $v !== null && $v !== ''; });
        $info->socials = !empty($socials) ? $socials : null;
        $info->shipping_methods = $request->input('shipping_methods');
        $info->payment_methods = $request->input('payment_methods');
        // Guardar password (si viene, se guarda hashed)
        if ($request->filled('password')) {
            $info->password = Hash::make($request->input('password'));
        }
        $info->save();
        return redirect()->route('company_info.show')->with('success', 'Información de la empresa actualizada.');
    }
}

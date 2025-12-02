<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Sucursal;

class SucursalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
     public function handle(Request $request, Closure $next): Response
    {
       // cargar todas las sucursales para admin o solo las del usuario si es necesario
        $sucursals = auth()->check() && auth()->user()->type === 'ADMINISTRADOR'
            ? Sucursal::where('status',1)->orderBy('name')->get()
            : (auth()->check() ? auth()->user()->sucursals()->where('status',1)->orderBy('name')->get() : collect());

        // compartir con todas las vistas
        view()->share('sucursals', $sucursals);
        view()->share('selected_sucursal', session('selected_sucursal'));

        // Si no existe selected_sucursal en sesión, poner la primera sucursal disponible
        if (! session()->has('selected_sucursal') && $sucursals->isNotEmpty()) {
            session(['selected_sucursal' => $sucursals->first()->id]);
            view()->share('selected_sucursal', session('selected_sucursal'));
        }

        return $next($request);
    }
}

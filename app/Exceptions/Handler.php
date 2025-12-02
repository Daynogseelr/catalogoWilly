<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Exception;

class Handler extends Exception
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Check if the request is an AJAX request
        if ($request->expectsJson()) {
            // Return JSON response for AJAX requests
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // Redirect to home page for non-AJAX requests
        return redirect()->intended(route('/'));
    }
}

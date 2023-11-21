<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyTest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKye = Config::get('VehicleConstants.API_KEY');
        $rapikye = (collect(($request->headers->all())['api-key']??"")->first());
        if ($apiKye != $rapikye) {            
            // abort(response()->json(
            //     [
            //         'status' => false,
            //         'authenticated' => false,
            //         'message' => "No Secure Request"
            //     ]
            // ));
        }
        return $next($request);
    }
}

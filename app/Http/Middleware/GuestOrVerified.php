<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestOrVerified
{
    public function handle(Request $request, Closure $next)
    {
        $bearerToken = $this->getBearerToken($request);

        if ($bearerToken !== null) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);

            if ($user !== null) {
                $request->user = $user;
            }
        }

        return $next($request);
    }

    private function getBearerToken(Request $request)
    {
        $authorizationHeader = $request->header('Authorization');

        if ($authorizationHeader !== null && strpos($authorizationHeader, 'Bearer ') !== false) {
            $token = substr($authorizationHeader, 7);
            return $token;
        }

        return null;
    }



}
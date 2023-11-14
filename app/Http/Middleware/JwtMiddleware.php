<?php

namespace App\Http\Middleware;
use Tymon\JWTAuth\Facades\JWTAuth;
use Closure;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException; // Import the TokenExpiredException
use Illuminate\Http\JsonResponse;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return $this->tokenExpiredResponse($e->getMessage());
        } catch (JWTException $e) {
            return $this->invalidTokenResponse($e->getMessage());
        }

        return $next($request);
    }

    protected function tokenExpiredResponse($message)
    {
        $response = [
            'message' => 'Token has expired',
            'status' => 401,
            'error' => $message
        ];

        return new JsonResponse(['response' => $response], 401);
    }

    protected function invalidTokenResponse($message)
    {
        $response = [
            'message' => 'Invalid token provided',
            'status' => 401,
            'error' => $message
        ];

        return new JsonResponse(['response' => $response], 401);
    }
}

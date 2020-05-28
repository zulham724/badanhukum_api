<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use App\Models\User;

class JWTMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'code' => 401,
                'error' => 'Token not provided.'
            ], 401);
        }

        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
            $user = User::where('username', $credentials->username)->firstOrFail();
            $request->request->add([
                'user' => $user,
                'auth' => [
                    'username' => $credentials->username,
                    'email' => $credentials->email
                ]
            ]);
        } catch(ExpiredException $e) {
            return response()->json([
                'code' => 401,
                'error' => 'Current session is expired.'
            ], 401);

        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'error' => 'User is not found'
            ], 404);
        } catch(\Exception $e) {
            return response()->json([
                'code' => 400,
                'error' => 'An error while decoding token.',
                'reason' => $e->getMessage()
            ], 400);
        }
        return $next($request);
    }
}

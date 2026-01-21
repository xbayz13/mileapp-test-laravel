<?php

namespace App\Http\Middleware;

use App\Helpers\ValidationHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateMongoObjectId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $param = 'id'): Response
    {
        $id = $request->route($param);

        if (!$id || !ValidationHelper::isValidMongoObjectId($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid task ID format',
            ], 400);
        }

        return $next($request);
    }
}

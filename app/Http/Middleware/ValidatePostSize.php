<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Middleware\ValidatePostSize as BaseValidatePostSize;

class ValidatePostSize extends BaseValidatePostSize
{
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'File too large. Maximum allowed size is 20MB.',
            ], 422);
        }
    }
}

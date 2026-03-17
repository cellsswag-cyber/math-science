<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_suspended) {
            $this->authService->logout();

            return redirect()
                ->route('login')
                ->with('error', 'Your account has been suspended.');
        }

        return $next($request);
    }
}

<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('crypto-deposit-create', function (Request $request): Limit {
            $limit = max(1, (int) config('crypto.deposit_rate_limit_per_minute', 5));
            $userId = (string) $request->input('user_id', 'guest');

            return Limit::perMinute($limit)->by(sprintf('%s|%s', $request->ip(), $userId));
        });
    }
}

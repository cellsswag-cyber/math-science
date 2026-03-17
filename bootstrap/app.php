<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('game:open')->everyMinute();
        $schedule->command('game:close')->everyMinute();
        $schedule->command('game:generate-result')->everyMinute();
        $schedule->command('game:calculate-winners')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

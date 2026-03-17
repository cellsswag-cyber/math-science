<?php

namespace App\Providers;

use App\Events\EntryPlaced;
use App\Events\GameClosed;
use App\Events\ResultDeclared;
use App\Events\WinnerCalculated;
use App\Listeners\LogEntryPlaced;
use App\Listeners\LogGameClosed;
use App\Listeners\LogResultDeclared;
use App\Listeners\LogWinnerCalculated;
use App\Listeners\NotifyEntryPlaced;
use App\Listeners\NotifyResultDeclared;
use App\Listeners\TrackDomainAnalytics;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        EntryPlaced::class => [
            LogEntryPlaced::class,
            NotifyEntryPlaced::class,
            TrackDomainAnalytics::class,
        ],
        GameClosed::class => [
            LogGameClosed::class,
            TrackDomainAnalytics::class,
        ],
        ResultDeclared::class => [
            LogResultDeclared::class,
            NotifyResultDeclared::class,
            TrackDomainAnalytics::class,
        ],
        WinnerCalculated::class => [
            LogWinnerCalculated::class,
            TrackDomainAnalytics::class,
        ],
    ];
}

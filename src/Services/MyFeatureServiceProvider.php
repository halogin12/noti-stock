<?php

namespace Ducnm\NotiStock\Services;

use Ducnm\NotiStock\Console\Commands\SuggetStockShare;
use Ducnm\NotiStock\Console\Commands\CloneStockShare;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Ducnm\NotiStock\Schedulers\NotiStockScheduler;

class MyFeatureServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

            // Đăng ký command
            $this->commands([
                SuggetStockShare::class,
                CloneStockShare::class,
            ]);

            // Gắn schedule
            $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
                NotiStockScheduler::schedule($schedule);
            });

        }

        
    }
}

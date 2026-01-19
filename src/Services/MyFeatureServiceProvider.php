<?php

namespace Halogin\NotiStock\Services;

use Halogin\NotiStock\Console\Commands\SuggetStockShare;
use Halogin\NotiStock\Console\Commands\CloneStockShare;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Halogin\NotiStock\Schedulers\NotiStockScheduler;

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

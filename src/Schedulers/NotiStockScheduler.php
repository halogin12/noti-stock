<?php

namespace Ducnm\NotiStock\Schedulers;

use Illuminate\Console\Scheduling\Schedule;

class NotiStockScheduler
{
    public static function schedule(Schedule $schedule): void
    {
        // Chạy 9h sáng mỗi ngày
        $schedule->command('app:clone-stock-shase')
            ->dailyAt('09:00');

        $schedule->command('app:sugget-stock-share')
            ->dailyAt('09:00');

        $schedule->command('app:sugget-stock-share')
            ->dailyAt('13:00');

        $schedule->command('app:sugget-stock-share')
            ->dailyAt('14:30');
    }
}

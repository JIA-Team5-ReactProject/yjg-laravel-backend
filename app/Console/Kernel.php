<?php

namespace App\Console;

use App\Models\RestaurantSemesterAuto;
use App\Models\RestaurantWeekendAuto;
use App\Tasks\RestaurantSemesterNotification;
use App\Tasks\RestaurantWeekendNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $semesterAuto = RestaurantSemesterAuto::query()->first('start_date');

        // Restaurant 에 자동설정된 값이 존재하면 수행
        if($semesterAuto) {
            $dateToArray = explode('-', $semesterAuto['start_date']);
            list($month, $date) = $dateToArray;
            $schedule->call(new RestaurantSemesterNotification)->cron('00 09 '.$date.' '.$month.' *'); // 자동설정 날짜 9시에 실행
        }

        $weekendAuto = RestaurantWeekendAuto::query()->first(['start_week','start_time']);

        if($weekendAuto) {
            $timeToArray = explode(':', $weekendAuto->start_time);
            $schedule->call(new RestaurantWeekendNotification)->cron($timeToArray[1].' '.$timeToArray[0].' * * '.$weekendAuto['start_week']);
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

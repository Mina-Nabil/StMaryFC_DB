<?php

namespace App\Console;

use App\Jobs\DeductMonthlySubscription;
use App\Models\Attendance;
use App\Models\User;
use App\Models\UserImage;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->call(function () {
        //     echo  "Image 
        //     CRON started \n ";
        //     $unCompressedUserImages = UserImage::get();
        //     foreach ($unCompressedUserImages as $image) {
        //         echo $image->id . ": started compressing.. \n ";
        //         $image->compress();
        //         echo $image->id . ": Compressing DONE ================== \n ";
        //     }
        // })->everyMinute();

        $schedule->call(function () {
            $players = User::with('player_category')->players()->get();
            $now = new Carbon();
            $now->subMonth();
            foreach($players as $player){
                DeductMonthlySubscription::dispatch($player, $now->format('Y'), $now->format('m'));
            }
        })->everyMinute(1, '0:05');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

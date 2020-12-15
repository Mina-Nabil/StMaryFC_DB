<?php

namespace App\Console;

use App\Models\UserImage;
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
        // $schedule->call(function (){
        //     $unCompressedUserImages = UserImage::where('USIM_CMPS', 0)->get();
        //     foreach($unCompressedUserImages as $image){
        //         $image->compress();
        //     }
        // })->everyMinute();
        $schedule->call(function (){
            $unCompressedUserImages = UserImage::where('USIM_USER_ID', 3)->get();
            $image = UserImage::findOrFail(4);
            $image->flipImage();
            
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

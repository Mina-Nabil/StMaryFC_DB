<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('app_users')->insert([
            "USER_NAME" => "mina",
            "USER_MAIL" => "mina",
            "USER_USTP_ID" => 1,
            "USER_PASS" => bcrypt('mina@stmary'),
            "USER_BDAY" => "1994-04-28",
            "USER_FACE_ID" => "NULL",
        ]);
    }
}

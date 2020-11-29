<?php

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
        // $this->call(UserSeeder::class);
        DB::table("dash_types")->insert([
            "DHTP_NAME" => "admin"
        ]);

        DB::table('dash_users')->insert([
            "DASH_USNM" => "mina",
            "DASH_FLNM" => "Mina Nabil",
            "DASH_PASS" => bcrypt('mina@stmary'),
            "DASH_TYPE_ID" => 1,
        ]);

        DB::table('dash_users')->insert([
            "DASH_USNM" => "admin",
            "DASH_FLNM" => "Mr Peter",
            "DASH_PASS" => bcrypt('stmaryadmin'),
            "DASH_TYPE_ID" => 1,
        ]);

        DB::table('app_user_types')->insert([
            'id' => 1,
            "USTP_NAME" => "Admin"
        ]);

        DB::table('app_user_types')->insert([
            'id' => 2,
            "USTP_NAME" => "Player"
        ]);

        DB::table('groups')->insert([
            'id' => 1,
            "GRUP_NAME" => "Admins"
        ]);
    }
}

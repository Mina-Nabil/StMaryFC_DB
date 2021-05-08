<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUser4Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("app_users", function (Blueprint $table) {
            $table->dropUnique("unique_user_code");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("app_users", function (Blueprint $table) {
            $table->unique("USER_CODE", "unique_user_code");
        });
    }
}

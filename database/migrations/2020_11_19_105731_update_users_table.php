<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("app_users", function (Blueprint $table){
            $table->string("USER_MOBN")->nullable();
            $table->string("USER_CMNT")->nullable();
            $table->string("USER_CODE")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("app_users", function (Blueprint $table){
            $table->dropColumn("USER_MOBN");
            $table->dropColumn("USER_CMNT");
            $table->dropColumn("USER_CODE");
        });
    }
}

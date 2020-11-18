<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersGroupsActive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_users', Function (Blueprint $table){
            $table->tinyInteger('USER_ACTV')->default(1);
        });
        Schema::table('groups', Function (Blueprint $table){
            $table->tinyInteger('GRUP_ACTV')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_users', Function (Blueprint $table){
            $table->dropColumn('USER_ACTV');
        });
        Schema::table('groups', Function (Blueprint $table){
            $table->tinyInteger('GRUP_ACTV');
        });
    }
}

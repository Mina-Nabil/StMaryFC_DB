<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUniformStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("app_users", function (Blueprint $table){
            $table->dropForeign("app_users_user_ufrm_id_foreign");
            $table->dropColumn("USER_UFRM_ID");
        });
        Schema::dropIfExists('uniform_states');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}

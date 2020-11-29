<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsers2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("app_users", function (Blueprint $table){
            $table->unique("USER_CODE", "unique_user_code");
            $table->foreignId("USER_UFRM_ID")->default(1)->constrained("uniform_states"); 
            $table->softDeletes();
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
            $table->dropUnique("unique_user_code");
            $table->dropForeign("app_users_user_ufrm_id_foreign");
            $table->dropColumn("USER_UFRM_ID");
            $table->dropSoftDeletes();
        });
    }
}

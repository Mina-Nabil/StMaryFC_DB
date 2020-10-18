<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_user_types', function (Blueprint $table) {
            $table->id();
            $table->string("USTP_NAME")->unique();
        });

        Schema::create('app_users', function (Blueprint $table) {
            $table->id();
            $table->string("USER_NAME")->unique();
            $table->foreignId("USER_USTP_ID")->constrained("app_user_types");
            $table->date("USER_BDAY")->nullable();;
            $table->string("USER_MAIL")->nullable();
            $table->string("USER_PASS")->nullable();
            $table->string("USER_FACE_ID")->unique();
            $table->foreignId("USER_MAIN_IMGE")->nullable();

        });

        Schema::create('app_user_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId("USIM_USER_ID")->constrained("app_users");
            $table->string("USIM_URL");
        });

        Schema::table('app_users', function (Blueprint $table){
            $table->foreign('USER_MAIN_IMGE')->on('app_user_images')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_users', function (Blueprint $table){
            $table->dropForeign('app_users_user_main_imge_foreign');
        });
        Schema::dropIfExists('app_user_images');
        Schema::dropIfExists('app_users');
        Schema::dropIfExists('app_user_types');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string("GRUP_NAME");
        });
        Schema::table('app_users', function (Blueprint $table){
            $table->foreignId('USER_GRUP_ID')->nullable()->constrained('groups');
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
            $table->dropForeign('app_users_user_grup_id_foreign');
        });
        Schema::dropIfExists('groups');
    }
}

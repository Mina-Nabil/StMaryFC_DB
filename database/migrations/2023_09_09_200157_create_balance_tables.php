<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId("BLNC_USER_ID")->constrained('app_users');
            $table->integer('BLNC_CHNG');
            $table->integer('BLNC_NEW');
            $table->integer('BLNC_OLD');
            $table->string('BLNC_TTLE');
            $table->string('BLNC_DESC')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('balance_tables');
    }
};

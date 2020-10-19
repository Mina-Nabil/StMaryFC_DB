<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('PYMT_USER_ID')->constrained('app_users');
            $table->double("PYMT_AMNT");
            $table->date("PYMT_DATE");
            $table->string("PYMT_NOTE")->nullable();
        });

        Schema::table('attendance', function (Blueprint $table){
            $table->tinyInteger('ATND_PAID')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance', function (Blueprint $table){
            $table->dropColumn('ATND_PAID');
        });
        Schema::dropIfExists('payments');
    }
}

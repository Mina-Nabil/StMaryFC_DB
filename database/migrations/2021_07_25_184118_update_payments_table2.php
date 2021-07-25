<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePaymentsTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("payments", function(Blueprint $table){
            $table->foreignId('PYMT_CLCT_ID')->nullable()->constrained('app_users');
        });
        Schema::table("event_payments", function(Blueprint $table){
            $table->foreignId('EVPY_CLCT_ID')->nullable()->constrained('app_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("payments", function(Blueprint $table){
            $table->dropForeign("app_users_pymt_user_id_foreign");
            $table->dropColumn("PYMT_CLCT_ID");
        });
        Schema::table("payments", function(Blueprint $table){
            $table->dropForeign("app_users_pymt_user_id_foreign");
            $table->dropColumn("EVPY_CLCT_ID");
        });
    }
}

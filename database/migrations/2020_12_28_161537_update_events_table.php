<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::dropIfExists("events_groups");

        Schema::table('events', function(Blueprint $table){
            $table->text('EVNT_CMNT')->nullable()->change();
        });

        Schema::table('events_attendance', function (Blueprint $table){
            $table->dropColumn('id');
            $table->primary(['EVAT_USER_ID', 'EVAT_EVNT_ID']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

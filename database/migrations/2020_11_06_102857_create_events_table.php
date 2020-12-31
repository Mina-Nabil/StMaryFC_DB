<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string("EVNT_NAME");
            $table->date('EVNT_DATE');
            $table->date('EVNT_END_DATE')->nullable();
            $table->double('EVNT_PRCE');
            $table->double('EVNT_CMNT')->nullable();
        });

        Schema::create('events_attendance', function (Blueprint $table){
            $table->id();
            $table->foreignId('EVAT_EVNT_ID')->constrained('events');
            $table->foreignId('EVAT_USER_ID')->constrained('app_users');
            $table->double('EVAT_STTS')->default(0); //0 7agaz bas - 1 7agaz w geh - 2 7agaz w cancel
        });

        Schema::create('event_payments', function (Blueprint $table){
            $table->id();
            $table->foreignId('EVPY_EVNT_ID')->constrained('events');
            $table->foreignId('EVPY_USER_ID')->constrained('app_users');
            $table->double('EVPY_AMNT');
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
        Schema::dropIfExists('event_payments');
        Schema::dropIfExists('events_attendance');
        Schema::dropIfExists('events_groups');
        Schema::dropIfExists('events');
    }
}

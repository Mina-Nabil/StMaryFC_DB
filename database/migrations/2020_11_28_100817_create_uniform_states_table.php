<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUniformStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uniform_states', function (Blueprint $table) {
            $table->id();
            $table->string("UFRM_NAME");
        });

        DB::table('uniform_states')->insert([
            'id' => 1,
            "UFRM_NAME" => "Fresh"
        ]);

        DB::table('uniform_states')->insert([
            'id' => 2,
            "UFRM_NAME" => "Paid Only"
        ]);

        DB::table('uniform_states')->insert([
            'id' => 3,
            "UFRM_NAME" => "Received Only"
        ]);

        DB::table('uniform_states')->insert([
            'id' => 4,
            "UFRM_NAME" => "Paid & Received"
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uniform_states');
    }
}

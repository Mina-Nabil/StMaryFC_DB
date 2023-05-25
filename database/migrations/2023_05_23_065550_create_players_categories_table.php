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
        Schema::create('players_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('desc')->nullable();
            $table->timestamps();
        });

        Schema::create('categories_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('players_category_id')->constrained('players_categories');
            $table->integer('attendance');
            $table->double('payment');
        });

        Schema::table('app_users', function (Blueprint $table) {
            $table->foreignId('players_category_id')->nullable()->constrained('players_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_users', function (Blueprint $table) {
            $table->dropForeign('app_users_players_category_id_foreign');
            $table->dropColumn('players_category_id');
        });
        Schema::dropIfExists('categories_details');
        Schema::dropIfExists('players_categories');
    }
};

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
        Schema::table("app_users", function (Blueprint $table) {
            $table->dateTime("balance")->default(0);
        });

        Schema::create('balance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId("app_users_id")->constrained('app_users');
            $table->foreignId("collected_by")->nullable()->constrained('app_users');
            $table->integer('value');
            $table->integer('new_balance');
            $table->string('title');
            $table->string('desc')->nullable();
            $table->boolean('is_settlment');
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
        Schema::table("app_users", function (Blueprint $table) {
            $table->dropColumn("balance");
        });

        Schema::dropIfExists('balance_payments');
    }
};

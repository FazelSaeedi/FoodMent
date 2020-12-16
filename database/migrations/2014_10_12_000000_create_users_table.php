<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();
            $table->string('password', 60)->default('null');
            $table->string('phone')->unique()->nullable(false);
            $table->string('token' , '1000')->nullable();
            $table->string('sms_code')->unique()->nullable(false);
            $table->boolean('registerd');
            $table->bigInteger('level_id')->default(1);
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
        Schema::dropIfExists('users');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaytrCards extends Migration
{
    public function up()
    {
        Schema::create('paytr_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('owner');
            $table->string('number');
            $table->integer('expire_month');
            $table->integer('expire_year');
            $table->integer('cvv');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paytr_cards');
    }
}

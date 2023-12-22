<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaytrTransactions extends Migration
{
    public function up()
    {
        Schema::create('paytr_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trans');
            $table->string('mapping');
            $table->integer('status')->default(0);
            $table->text('html')->nullable();
            $table->float('amount')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paytr_transactions');
    }
}

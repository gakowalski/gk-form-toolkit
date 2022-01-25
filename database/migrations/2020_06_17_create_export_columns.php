<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExportColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('export_columns', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();;
            $table->timestamps();
            $table->string('path');
            $table->string('name');
            $table->string('group_name');
            $table->integer('order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('export_columns');
    }
}

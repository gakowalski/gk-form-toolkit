<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExportColumns extends Migration
{
    const table = 'export_columns';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this::table))
          return;

        Schema::create($this::table, function (Blueprint $table) {
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
        Schema::dropIfExists($this::table);
    }
}

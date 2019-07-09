<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCargoTypeRemuneracionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cargo_type_remuneracion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('cargo_id');
            $table->integer('type_remuneracion_id');
            $table->text("observacion")->nullable();
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
        Schema::dropIfExists('cargo_type_remuneracion');
    }
}
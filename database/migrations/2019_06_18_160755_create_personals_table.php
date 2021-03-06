<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug')->nullable();
            $table->integer("sede_id");
            $table->string("dependencia_txt");
            $table->string("cargo_txt")->nullable();
            $table->integer("cantidad")->default(1);
            $table->string("honorarios")->nullable();
            $table->integer("meta_id");
            $table->string("deberes")->nullable();
            $table->date("fecha_inicio")->nullable();
            $table->date("fecha_final")->nullable();
            $table->string("lugar_txt")->nullable();
            $table->text("bases")->nullable();
            $table->string("supervisora_txt")->nullable();
            $table->integer('aceptado')->default(0);
            $table->integer("convocatoria_id")->nullable();
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
        Schema::dropIfExists('personals');
    }
}

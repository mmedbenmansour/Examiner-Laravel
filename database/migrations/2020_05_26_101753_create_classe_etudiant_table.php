<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClasseEtudiantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classe_etudiant', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('etudiant_id');
            $table->unsignedBigInteger('classe_id');
            $table->foreign('etudiant_id')
                ->references('id')->on('etudiants')
                ->onDelete('cascade');
            $table->foreign('classe_id')
                ->references('id')->on('classes')
                ->onDelete('cascade');
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
        Schema::dropIfExists('classe_etudiant');
    }
}

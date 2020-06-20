<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEtudiantExamensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etudiant_examens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('note');
            $table->unsignedBigInteger('etudiant_id');
            $table->unsignedBigInteger('examen_id');

            $table->foreign('etudiant_id')
                ->references('id')->on('etudiants')
                ->onDelete('cascade');

            $table->foreign('examen_id')
                ->references('id')->on('examens')
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
        Schema::dropIfExists('etudiant_examens');
    }
}

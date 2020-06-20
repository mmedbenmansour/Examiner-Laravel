<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReponseEtudiantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reponse_etudiants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('texte');
            $table->string('url');
            $table->string('choix');
            $table->double('note');
            $table->unsignedBigInteger('etudiant_id');
            $table->unsignedBigInteger('question_id');

            $table->foreign('etudiant_id')
                ->references('id')->on('etudiants')
                ->onDelete('cascade');
            $table->foreign('question_id')
                ->references('id')->on('questions')
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
        Schema::dropIfExists('reponse_etudiants');
    }
}

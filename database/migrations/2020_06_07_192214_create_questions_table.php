<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('numero');
            $table->string('contenu');
            $table->double('note');
            $table->enum('type_question',['qcm','ouverte','fichier']);
            $table->unsignedBigInteger('solution_id');
            $table->unsignedBigInteger('examen_id');




            $table->timestamps();
        });

        Schema::create('solutions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('texte');
            $table->string('url');
            $table->string('choix');
            $table->unsignedBigInteger('question_id');

            $table->timestamps();
        });

        Schema::table('solutions', function($table)
        {
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });
        Schema::table('questions', function($table)
        {
            $table->foreign('solution_id')
                ->references('id')->on('solutions')
                ->onDelete('cascade');
            $table->foreign('examen_id')
                ->references('id')->on('examens')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');

    }
}

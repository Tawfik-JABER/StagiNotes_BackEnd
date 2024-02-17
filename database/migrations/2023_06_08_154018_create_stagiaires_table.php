<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stagiaires', function (Blueprint $table) {
            $table->id();
            $table->string("cin");
            $table->string("nom");
            $table->string("prenom");
            $table->string("email");
            $table->string("password");

            $table->unsignedBigInteger('fill_id');
            $table->foreign('fill_id')->references('id')->on('fillieres')
            ->onDelete('cascade')
            ->onUpdate("cascade");

            $table->integer("numero");
            $table->string("cef");
            $table->string("group");
            $table->string("annee");
            $table->string("niveau");
            $table->string("sexe");
            $table->string("email_verify")->nullable();
            $table->date("login_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stagiaires');
    }
};

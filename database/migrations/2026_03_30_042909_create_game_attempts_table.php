<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('award_level');
            $table->unsignedInteger('total_questions');
            $table->unsignedInteger('correct_answers');
            $table->unsignedInteger('incorrect_answers');
            $table->unsignedInteger('time_remaining')->default(0);
            $table->timestamps();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_attempt_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('table_number');
            $table->unsignedTinyInteger('multiplier');
            $table->string('operator');
            $table->string('user_input_type');
            $table->boolean('is_correct');
        });
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'award_level',
        'total_questions',
        'correct_answers',
        'incorrect_answers',
        'time_remaining',
    ];

    protected function casts(): array
    {
        return [
            'total_questions' => 'integer',
            'correct_answers' => 'integer',
            'incorrect_answers' => 'integer',
            'time_remaining' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

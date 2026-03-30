<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAttemptAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'game_attempt_id',
        'table_number',
        'multiplier',
        'operator',
        'user_input_type',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'table_number' => 'integer',
            'multiplier' => 'integer',
            'is_correct' => 'boolean',
        ];
    }

    public function gameAttempt(): BelongsTo
    {
        return $this->belongsTo(GameAttempt::class);
    }
}

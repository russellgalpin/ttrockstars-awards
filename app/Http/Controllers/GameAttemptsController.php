<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GameAttemptsController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'award_level' => ['required', 'in:bronze,silver,gold'],
            'total_questions' => ['required', 'integer', 'min:1'],
            'correct_answers' => ['required', 'integer', 'min:0'],
            'incorrect_answers' => ['required', 'integer', 'min:0'],
            'time_remaining' => ['required', 'integer', 'min:0'],
        ]);

        $request->user()->gameAttempts()->create($validated);

        return back();
    }
}

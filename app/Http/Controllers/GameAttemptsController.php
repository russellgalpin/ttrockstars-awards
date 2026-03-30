<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGameAttemptRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class GameAttemptsController extends Controller
{
    public function store(StoreGameAttemptRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $attempt = $request->user()->gameAttempts()->create(
            Arr::except($validated, ['answers'])
        );

        $attempt->answers()->createMany($validated['answers']);

        return back();
    }
}

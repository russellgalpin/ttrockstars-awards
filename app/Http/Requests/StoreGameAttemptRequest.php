<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'award_level' => ['required', 'in:bronze,silver,gold'],
            'total_questions' => ['required', 'integer', 'min:1'],
            'correct_answers' => ['required', 'integer', 'min:0'],
            'incorrect_answers' => ['required', 'integer', 'min:0'],
            'time_remaining' => ['required', 'integer', 'min:0'],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.table_number' => ['required', 'integer', 'min:1', 'max:12'],
            'answers.*.multiplier' => ['required', 'integer', 'min:1', 'max:12'],
            'answers.*.operator' => ['required', 'in:×,÷'],
            'answers.*.user_input_type' => ['required', 'in:operand1,operand2,answer'],
            'answers.*.is_correct' => ['required', 'boolean'],
        ];
    }
}

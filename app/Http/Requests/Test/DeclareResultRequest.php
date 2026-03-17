<?php

namespace App\Http\Requests\Test;

use Illuminate\Foundation\Http\FormRequest;

class DeclareResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'winning_number' => ['nullable', 'integer', 'between:0,99'],
        ];
    }
}

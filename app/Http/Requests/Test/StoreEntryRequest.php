<?php

namespace App\Http\Requests\Test;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'prediction_number' => ['required', 'integer', 'between:0,99'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}

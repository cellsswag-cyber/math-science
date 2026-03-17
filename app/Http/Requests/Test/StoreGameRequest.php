<?php

namespace App\Http\Requests\Test;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'open_time' => ['required', 'date'],
            'close_time' => ['required', 'date'],
            'result_time' => ['required', 'date'],
        ];
    }
}

<?php

namespace App\Http\Requests\Crypto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateDepositRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'price_currency' => ['nullable', 'string', Rule::in(['usd', 'inr', 'USD', 'INR'])],
            'pay_currency' => ['nullable', 'string', Rule::in(['usdttrc20', 'USDTTRC20'])],
            'order_description' => ['nullable', 'string', 'max:255'],
        ];
    }
}

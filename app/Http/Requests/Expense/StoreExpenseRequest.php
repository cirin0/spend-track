<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|integer|min:1',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date|before_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be greater than 0',
            'amount.max' => 'Amount is too large',
            'date.required' => 'Date is required',
            'date.before_or_equal' => 'Date cannot be in the future',
        ];
    }
}

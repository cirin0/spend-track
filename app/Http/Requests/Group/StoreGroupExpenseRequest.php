<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|integer|exists:group_categories,id',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'currency' => 'required|string|in:UAH,EUR,USD',
            'converted_amount' => 'required|numeric|min:0.01',
            'exchange_rate' => 'required|numeric|min:0.0001',
            'description' => 'nullable|string|max:500',
            'date' => 'required|date|before_or_equal:today'
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be greater than 0',
            'amount.max' => 'Amount is too large',
            'currency.required' => 'Currency is required',
            'currency.in' => 'Currency must be one of: UAH, EUR, USD',
            'converted_amount.required' => 'Converted amount is required',
            'converted_amount.min' => 'Converted amount must be greater than 0',
            'exchange_rate.required' => 'Exchange rate is required',
            'exchange_rate.min' => 'Exchange rate must be greater than 0',
            'date.required' => 'Date is required',
            'date.before_or_equal' => 'Date cannot be in the future',
        ];
    }
}

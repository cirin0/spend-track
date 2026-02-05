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
            'category_id' => 'required|integer|min:1',
            'category_type' => 'required|in:default,user',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date|before_or_equal:today',
        ];
    }


    public function messages(): array
    {
        return [
            'category_id.required' => 'Category is required',
            'category_type.required' => 'Category type is required',
            'category_type.in' => 'Invalid category type',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be greater than 0',
            'amount.max' => 'Amount is too large',
            'date.required' => 'Date is required',
            'date.before_or_equal' => 'Date cannot be in the future',
        ];
    }
}

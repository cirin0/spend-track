<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseIndexRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'start_date' => $this->input('start_date', $this->input('from')),
            'end_date' => $this->input('end_date', $this->input('to')),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category' => 'nullable|integer|min:1',
        ];
    }

    public function fromDate(): ?string
    {
        return $this->input('start_date');
    }

    public function toDate(): ?string
    {
        return $this->input('end_date');
    }

    public function categoryId(): ?int
    {
        $categoryId = $this->input('category');

        return $categoryId !== null ? (int) $categoryId : null;
    }
}

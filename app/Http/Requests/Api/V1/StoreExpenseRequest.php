<?php

namespace App\Http\Requests\Api\V1;

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
            'outlet_id'           => ['required', 'integer', 'exists:outlets,id'],
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'amount'              => ['required', 'numeric', 'min:0'],
            'description'         => ['nullable', 'string', 'max:500'],
            'expense_date'        => ['required', 'date'],
            'receipt_image'       => ['nullable', 'image', 'max:2048'],
        ];
    }
}

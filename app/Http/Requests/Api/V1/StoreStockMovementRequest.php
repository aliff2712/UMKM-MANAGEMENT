<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type'       => ['required', 'in:in,out,adjustment'],
            'qty'        => ['required', 'integer'],
            'note'       => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Tipe pergerakan stok harus: in, out, atau adjustment.',
        ];
    }
}

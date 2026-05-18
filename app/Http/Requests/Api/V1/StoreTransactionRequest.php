<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'outlet_id'         => ['required', 'integer', 'exists:outlets,id'],
            'payment_method'    => ['required', 'in:cash,transfer,qris'],
            'discount_amount'   => ['nullable', 'numeric', 'min:0'],
            'paid_amount'       => ['required', 'numeric', 'min:0'],
            'note'              => ['nullable', 'string', 'max:500'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'            => 'Minimal satu produk harus dipilih.',
            'items.*.product_id.exists' => 'Produk tidak ditemukan.',
            'items.*.qty.min'           => 'Jumlah produk minimal 1.',
        ];
    }
}

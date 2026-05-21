<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'outlet_id'      => ['required', 'integer', 'exists:outlets,id'],
            'category_id'    => ['nullable', 'integer', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:255'],
            'sku'            => ['required', 'string', 'max:100', 'unique:products,sku,' . $productId],
            'unit'           => ['required', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price'  => ['required', 'numeric', 'min:0'],
            'stock_qty'      => ['required', 'integer', 'min:0'],
            'stock_minimum'  => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048', 'dimensions:max_width=1920,max_height=1920'],
        ];
    }
}

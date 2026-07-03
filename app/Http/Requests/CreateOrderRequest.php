<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_uuid' => ['required', 'uuid', 'exists:products,uuid'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.discount' => ['required', 'numeric', 'between:0,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'products.required' => 'Ürün satırları zorunludur.',
            'products.min' => 'Sipariş en az bir ürün satırı içermelidir.',
            'products.*.discount.between' => 'İndirim 0 ile 100 arasında olmalıdır.',
        ];
    }
}

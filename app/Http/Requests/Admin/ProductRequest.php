<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        $mode = $this->input('mode');

        $sells = in_array($mode, [
            Product::MODE_SALE,
            Product::MODE_BOTH,
        ], true);

        $rents = in_array($mode, [
            Product::MODE_RENT,
            Product::MODE_BOTH,
        ], true);

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id'),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'mode' => [
                'required',
                Rule::in(Product::MODES),
            ],

            'sale_price' => $sells
                ? ['required', 'integer', 'min:1', 'max:1000000000']
                : ['nullable', 'integer', 'min:0'],

            'stock_sale' => $sells
                ? ['required', 'integer', 'min:0', 'max:100000']
                : ['nullable', 'integer', 'min:0'],

            'rent_price_per_day' => $rents
                ? ['required', 'integer', 'min:1', 'max:1000000000']
                : ['nullable', 'integer', 'min:0'],

            'deposit' => $rents
                ? ['required', 'integer', 'min:0', 'max:1000000000']
                : ['nullable', 'integer', 'min:0'],

            'stock_rent' => $rents
                ? ['required', 'integer', 'min:0', 'max:100000']
                : ['nullable', 'integer', 'min:0'],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'remove_image' => ['nullable', 'boolean'],

            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'kategori',
            'name' => 'nama produk',
            'description' => 'deskripsi produk',
            'mode' => 'mode produk',
            'sale_price' => 'harga jual',
            'stock_sale' => 'stok jual',
            'rent_price_per_day' => 'harga sewa per hari',
            'deposit' => 'deposit',
            'stock_rent' => 'stok sewa',
            'image' => 'gambar',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa angka bulat.',
            'string' => ':attribute harus berupa teks.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max.',
            'exists' => ':attribute tidak valid.',
            'in' => ':attribute tidak valid.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
        ];
    }
}

<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'type' => ['required', Rule::in(['sale', 'rent'])],
            'qty' => ['required', 'integer', 'min:1', 'max:100'],
            'rent_start' => ['required_if:type,rent', 'nullable', 'date', 'after_or_equal:today'],
            'rent_end' => ['required_if:type,rent', 'nullable', 'date', 'after_or_equal:rent_start'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'produk',
            'type' => 'tipe pembelian',
            'qty' => 'jumlah',
            'rent_start' => 'tanggal mulai sewa',
            'rent_end' => 'tanggal selesai sewa',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi untuk penyewaan.',
            'integer' => ':attribute harus berupa angka bulat.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max.',
            'date' => ':attribute tidak valid.',
            'in' => ':attribute tidak valid.',
            'exists' => ':attribute tidak ditemukan.',
            'rent_start.after_or_equal' => 'Tanggal mulai sewa tidak boleh sebelum hari ini.',
            'rent_end.after_or_equal' => 'Tanggal selesai sewa tidak boleh sebelum tanggal mulai.',
        ];
    }
}
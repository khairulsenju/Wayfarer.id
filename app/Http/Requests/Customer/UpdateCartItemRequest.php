<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
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
            'qty' => ['required', 'integer', 'min:1', 'max:100'],
            'rent_start' => ['nullable', 'date', 'after_or_equal:today'],
            'rent_end' => ['nullable', 'date', 'after_or_equal:rent_start'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
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
            'integer' => ':attribute harus berupa angka bulat.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max.',
            'date' => ':attribute tidak valid.',
            'rent_start.after_or_equal' => 'Tanggal mulai sewa tidak boleh sebelum hari ini.',
            'rent_end.after_or_equal' => 'Tanggal selesai sewa tidak boleh sebelum tanggal mulai.',
        ];
    }
}
<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'recipient_name' => 'nama penerima',
            'phone' => 'nomor telepon',
            'address' => 'alamat',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'address.min' => 'Alamat minimal 10 karakter.',
            'phone.regex' => 'Nomor telepon harus 8 sampai 20 karakter berupa angka, spasi, tanda + atau -.',
        ];
    }
}
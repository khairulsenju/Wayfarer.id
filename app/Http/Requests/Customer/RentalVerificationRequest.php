<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class RentalVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() === true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s]{8,20}$/',
            ],

            'ktp' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'ktp.required' => 'Foto KTP wajib diunggah.',
            'ktp.image' => 'File harus berupa gambar.',
            'ktp.mimes' => 'Format KTP harus JPG, JPEG, atau PNG.',
            'ktp.max' => 'Ukuran foto KTP maksimal 5 MB.',
        ];
    }
}
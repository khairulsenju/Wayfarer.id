<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\RentalVerification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class RentalVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $verification = $user->rentalVerification;

        return view('customer.rental-verification.index', [
            'user' => $user,
            'verification' => $verification,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $existing = $user->rentalVerification;

        if ($existing && $existing->isApproved()) {
            return back()->with(
                'success',
                'Identitas kamu sudah terverifikasi.'
            );
        }

        if (
            $existing
            && $existing->status === RentalVerification::STATUS_PENDING
        ) {
            return back()->with(
                'error',
                'Pengajuan kamu masih menunggu pemeriksaan admin.'
            );
        }

        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s]{8,20}$/',
            ],
            'ktp' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],
        ], [
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'ktp.required' => 'Foto KTP wajib diunggah.',
            'ktp.mimes' => 'Foto KTP harus JPG, JPEG, atau PNG.',
            'ktp.max' => 'Ukuran foto KTP maksimal 5 MB.',
        ]);

        $newPath = $request->file('ktp')->store(
            'rental-ktp',
            'local'
        );

        if (! $newPath) {
            return back()->with(
                'error',
                'Gagal menyimpan dokumen KTP.'
            );
        }

        if ($existing && $existing->ktp_path) {
            Storage::disk('local')->delete($existing->ktp_path);
        }

        $user->update([
            'phone' => $validated['phone'],
        ]);

        RentalVerification::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'ktp_path' => $newPath,
                'status' => RentalVerification::STATUS_PENDING,
                'phone_verified_at' => null,
                'phone_verified_by' => null,
                'admin_notes' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        return redirect()
            ->route('rental-verification.index')
            ->with(
                'success',
                'Dokumen berhasil dikirim. Tunggu pemeriksaan admin.'
            );
    }
}

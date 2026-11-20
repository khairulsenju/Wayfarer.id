<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentalVerification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class RentalVerificationController extends Controller
{
    /**
     * Menampilkan daftar verifikasi penyewa.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');

        $verifications = RentalVerification::with('user')
            ->when(
                in_array($status, ['pending', 'approved', 'rejected'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.rental-verifications.index', [
            'verifications' => $verifications,
        ]);
    }

    /**
     * Menampilkan foto KTP dengan respons file yang benar.
     */
    public function showKtp(RentalVerification $verification)
    {
        $disk = Storage::disk('local');

        abort_unless(
            $verification->ktp_path
            && $disk->exists($verification->ktp_path),
            404
        );

        $path = $disk->path($verification->ktp_path);

        $mime = mime_content_type($path);

        abort_unless(
            in_array($mime, ['image/jpeg', 'image/png'], true),
            404
        );

        return response()->file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Menyetujui verifikasi penyewa.
     */
    public function approve(
        Request $request,
        RentalVerification $verification
    ): RedirectResponse {

        $request->validate([
            'phone_confirmation' => ['required', 'accepted'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (
            ! $verification->ktp_path
            || ! Storage::disk('local')->exists($verification->ktp_path)
        ) {
            return back()->with(
                'error',
                'Dokumen KTP tidak ditemukan. Pelanggan harus mengunggah ulang.'
            );
        }

        $verification->update([
            'status' => RentalVerification::STATUS_APPROVED,
            'phone_verified_at' => now(),
            'phone_verified_by' => $request->user()->id,
            'admin_notes' => $request->input('admin_notes'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with(
            'success',
            'Verifikasi pelanggan berhasil disetujui.'
        );
    }

    /**
     * Menolak pengajuan verifikasi penyewa.
     */
    public function reject(
        Request $request,
        RentalVerification $verification
    ): RedirectResponse {

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:2000'],
        ], [
            'admin_notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $verification->update([
            'status' => RentalVerification::STATUS_REJECTED,
            'phone_verified_at' => null,
            'phone_verified_by' => null,
            'admin_notes' => $validated['admin_notes'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with(
            'success',
            'Pengajuan verifikasi berhasil ditolak.'
        );
    }
}

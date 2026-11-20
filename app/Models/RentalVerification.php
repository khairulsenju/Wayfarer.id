<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalVerification extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'ktp_path',
        'status',
        'phone_verified_at',
        'phone_verified_by',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function phoneVerifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'phone_verified_by'
        );
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED
            && $this->phone_verified_at !== null
            && ! empty($this->ktp_path);
    }
}

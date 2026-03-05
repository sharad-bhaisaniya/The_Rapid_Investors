<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class UserAgreement extends Model  implements HasMedia
{
    use InteractsWithMedia;
      protected $fillable = [
        'user_id',
        'subscription_id',
        'invoice_id',
        'invoice_number',
        'agreement_number',
        'signed_at',
        'user_snapshot',
        'kyc_snapshot',
        'subscription_snapshot',
        'invoice_snapshot',
        'is_signed',
        'status',
    ];

    protected $casts = [
        'user_snapshot' => 'array',
        'kyc_snapshot' => 'array',
        'subscription_snapshot' => 'array',
        'invoice_snapshot' => 'array',
        'signed_at' => 'datetime',
        'is_signed' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('agreement_pdf')
            ->singleFile()
            ->useDisk('public');
    }

      // Agreement → User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Agreement → Invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    // Agreement → KYC
    public function kyc()
    {
        return $this->belongsTo(KycVerification::class, 'user_id', 'user_id');
        // relation via user_id
    }
}

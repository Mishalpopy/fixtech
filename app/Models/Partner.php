<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Partner extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $guard = 'partner';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'company_name',
        'trade_license_no',
        'status',
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_created_at',
        'is_approved',
        'is_pending',
        'is_rejected'
    ];

    public function attachments()
    {
        return $this->hasMany(PartnerAttachment::class);
    }

    public function emiratesIdAttachment()
    {
        return $this->hasOne(PartnerAttachment::class)->where('type', 'emirates_id');
    }

    public function visaAttachment()
    {
        return $this->hasOne(PartnerAttachment::class)->where('type', 'visa');
    }

    public function nocAttachment()
    {
        return $this->hasOne(PartnerAttachment::class)->where('type', 'noc');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getFormattedCreatedAtAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getIsApprovedAttribute()
    {
        return $this->approval_status === 'approved';
    }

    public function getIsPendingAttribute()
    {
        return $this->approval_status === 'pending';
    }

    public function getIsRejectedAttribute()
    {
        return $this->approval_status === 'rejected';
    }
}


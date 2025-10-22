<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PartnerAttachment extends Model
{
    protected $fillable = [
        'partner_id',
        'type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type'
    ];

    protected $appends = ['file_url'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    public function deleteFile()
    {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }
}


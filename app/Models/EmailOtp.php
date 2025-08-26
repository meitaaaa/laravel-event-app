<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code_hash',
        'type',
        'expires_at',
        'used_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime'
    ];

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isUsed()
    {
        return !is_null($this->used_at);
    }

    public function markAsUsed()
    {
        $this->update(['used_at' => now()]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

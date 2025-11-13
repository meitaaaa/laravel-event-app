<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'token_hash',
        'token_plain',
        'token_sent_at',
        'status',
        'name',
        'email',
        'phone',
        'motivation'
    ];

    protected $casts = [
        'token_sent_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class)->latest();
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}

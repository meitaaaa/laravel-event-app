<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'event_id',
        'user_id',
        'token_entered',
        'status',
        'attendance_time'
    ];

    protected $casts = [
        'attendance_time' => 'datetime'
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'flyer_path',
        'certificate_template_path',
        'is_published',
        'registration_closes_at',
        'created_by'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'event_date' => 'date',
        'registration_closes_at' => 'datetime'
    ];

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}

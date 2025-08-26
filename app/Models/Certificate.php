<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'registration_id',
        'serial_number',
        'file_path',
        'issued_at'
    ];

    protected $casts = [
        'issued_at' => 'datetime'
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}

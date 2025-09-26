<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'rating',
        'comment',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'rating' => 'integer',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}

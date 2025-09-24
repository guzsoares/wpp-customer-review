<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'appointment_datetime',
        'service_type',
        'status',
    ];

    protected $casts = [
        'appointment_datetime' => 'datetime',
    ];

    public function reviewRequest()
    {
        return $this->hasOne(ReviewRequest::class);
    }

    public function customerReview()
    {
        return $this->hasOne(CustomerReview::class);
    }
}

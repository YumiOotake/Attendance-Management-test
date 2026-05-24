<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_request_id',
        'requested_break_start',
        'requested_break_end',
    ];

    protected $casts = [
        'attendance_request_id' => 'integer',
    ];

    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }
}

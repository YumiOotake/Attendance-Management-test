<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function attendanceRequest(): BelongsTo
    {
        return $this->belongsTo(AttendanceRequest::class);
    }

    public function getFormattedBreakStartAttribute(): string|null
    {
        return $this->requested_break_start ? substr($this->requested_break_start, 0, 5) : null;
    }

    public function getFormattedBreakEndAttribute(): string|null
    {
        return $this->requested_break_end ? substr($this->requested_break_end, 0, 5) : null;
    }
}

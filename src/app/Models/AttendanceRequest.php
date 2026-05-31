<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_clock_in',
        'requested_clock_out',
        'note',
        'status',
    ];

    protected $casts = [
        'attendance_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestBreakTimes()
    {
        return $this->hasMany(RequestBreakTime::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            1 => '承認待ち',
            2 => '承認済み',
            default => '承認待ち',
        };
    }

    public function getFormattedClockInAttribute()
    {
        return $this->requested_clock_in ? substr($this->requested_clock_in, 0, 5) : null;
    }

    public function getFormattedClockOutAttribute()
    {
        return $this->requested_clock_out ? substr($this->requested_clock_out, 0, 5) : null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->clock_out) return 'done';
        if ($this->breakTimes->whereNull('break_end')->count() > 0) return 'break';
        if ($this->clock_in) return 'working';
        return 'none';
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function getFormattedClockInAttribute()
    {
        return $this->clock_in ? substr($this->clock_in, 0, 5) : null;
    }

    public function getFormattedClockOutAttribute()
    {
        return $this->clock_out ? substr($this->clock_out, 0, 5) : null;
    }

    public function getTotalBreakTimeAttribute()
    {
        $totalMinutes = 0;

        foreach ($this->breakTimes as $break) {
            if ($break->break_start && $break->break_end) {
                $totalMinutes += Carbon::parse($break->break_end)
                    ->diffInMinutes(Carbon::parse($break->break_start));
            }
        }
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getTotalWorkTimeAttribute()
    {
        $totalBreakMinutes = 0;
        $totalMinutes = 0;

        foreach ($this->breakTimes as $break) {
            if ($break->break_start && $break->break_end) {
                $totalBreakMinutes += Carbon::parse($break->break_end)
                    ->diffInMinutes(Carbon::parse($break->break_start));
            }
        }
        if ($this->clock_in && $this->clock_out) {
            $totalMinutes += Carbon::parse($this->clock_in)
                ->diffInMinutes(Carbon::parse($this->clock_out));
        }

        $totalWorkMinutes = $totalMinutes - $totalBreakMinutes;

        $hours = intdiv($totalWorkMinutes, 60);
        $minutes = $totalWorkMinutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getTotalBreakMinute(): int
    {
        $totalMinutes = 0;

        foreach ($this->breakTimes as $break) {
            if ($break->break_start && $break->break_end) {
                $totalMinutes += Carbon::parse($break->break_end)
                    ->diffInMinutes(Carbon::parse($break->break_start));
            }
        }

        return $totalMinutes;
    }

    public function getTotalWorkMinutes(): int
    {
        $totalBreakMinutes = 0;
        $totalMinutes = 0;

        foreach ($this->breakTimes as $break) {
            if ($break->break_start && $break->break_end) {
                $totalBreakMinutes += Carbon::parse($break->break_end)
                    ->diffInMinutes(Carbon::parse($break->break_start));
            }
        }
        if ($this->clock_in && $this->clock_out) {
            $totalMinutes += Carbon::parse($this->clock_in)
                ->diffInMinutes(Carbon::parse($this->clock_out));
        }

        $totalWorkMinutes = $totalMinutes - $totalBreakMinutes;

        return $totalWorkMinutes;
    }
}

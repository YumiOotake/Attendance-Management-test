<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'comment',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRequests(): HasMany
    {
        return $this->hasMany(AttendanceRequest::class);
    }

    public function breakTimes(): HasMany
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

    public function getFormattedClockInAttribute(): string|null
    {
        return $this->clock_in ? substr($this->clock_in, 0, 5) : null;
    }

    public function getFormattedClockOutAttribute(): string|null
    {
        return $this->clock_out ? substr($this->clock_out, 0, 5) : null;
    }

    public function getFormattedTotalBreakTimeAttribute(): string
    {
        $totalMinutes = 0;

        foreach ($this->breakTimes as $break) {
            if ($break->break_start && $break->break_end) {
                $totalMinutes += self::minutesBetween($break->break_start, $break->break_end);
            }
        }
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getFormattedTotalWorkTimeAttribute(): string
    {
        $totalBreakMinutes = 0;
        $totalMinutes = 0;

        foreach ($this->breakTimes as $break) {
            if ($break->break_start && $break->break_end) {
                $totalBreakMinutes += self::minutesBetween($break->break_start, $break->break_end);
            }
        }
        if ($this->clock_in && $this->clock_out) {
            $totalMinutes += self::minutesBetween($this->clock_in, $this->clock_out);
        }

        $totalWorkMinutes = $totalMinutes - $totalBreakMinutes;

        $hours = intdiv($totalWorkMinutes, 60);
        $minutes = $totalWorkMinutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getTotalWorkMinutes(): int
    {
        $totalBreakMinutes = 0;
        $totalMinutes = 0;

        foreach ($this->breakTimes as $break) {
            if ($break->break_start && $break->break_end) {
                $totalBreakMinutes += self::minutesBetween($break->break_start, $break->break_end);
            }
        }
        if ($this->clock_in && $this->clock_out) {
            $totalMinutes += self::minutesBetween($this->clock_in, $this->clock_out);
        }

        $totalWorkMinutes = $totalMinutes - $totalBreakMinutes;

        return $totalWorkMinutes;
    }

    public function getTotalBreakTimeAttribute(): string
    {
        $totalMinutes = 0;

        foreach ($this->breakTimes as $break) {
            if ($break->break_start && $break->break_end) {
                $totalMinutes += self::minutesBetween($break->break_start, $break->break_end);
            }
        }
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getTotalTimeAttribute(): string
    {
        $totalBreakMinutes = 0;
        $totalMinutes = 0;

        foreach ($this->breakTimes as $break) {
            if ($break->break_start && $break->break_end) {
                $totalBreakMinutes += self::minutesBetween($break->break_start, $break->break_end);
            }
        }
        if ($this->clock_in && $this->clock_out) {
            $totalMinutes += self::minutesBetween($this->clock_in, $this->clock_out);
        }

        $totalWorkMinutes = $totalMinutes - $totalBreakMinutes;

        $hours = intdiv($totalWorkMinutes, 60);
        $minutes = $totalWorkMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Carbon 3 の diffInMinutes は符号付き・float を返すため、整数の絶対値に揃える
     */
    private static function minutesBetween(string $from, string $to): int
    {
        return (int) abs(Carbon::parse($from)->diffInMinutes(Carbon::parse($to)));
    }
}

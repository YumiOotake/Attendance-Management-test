<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestBreakTimes(): HasMany
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

    public function getFormattedClockInAttribute(): string|null
    {
        return $this->requested_clock_in ? substr($this->requested_clock_in, 0, 5) : null;
    }

    public function getFormattedClockOutAttribute(): string|null
    {
        return $this->requested_clock_out ? substr($this->requested_clock_out, 0, 5) : null;
    }
}

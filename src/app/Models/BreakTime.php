<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end'
    ];

    protected $casts = [
        'attendance_id' => 'integer',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function getFormattedBreakStartAttribute(): string|null
    {
        return $this->break_start ? substr($this->break_start, 0, 5) : null;
    }

    public function getFormattedBreakEndAttribute(): string|null
    {
        return $this->break_end ? substr($this->break_end, 0, 5) : null;
    }
}

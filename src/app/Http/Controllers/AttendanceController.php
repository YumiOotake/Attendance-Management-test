<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * 勤怠登録画面を表示
     *
     * @return View
     */
    public function showAttendancePage(): View
    {
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', $now->today())
            ->first();

        return view('attendance.index', compact('now', 'attendance'));
    }

    /**
     * 出勤時刻を登録
     *
     * @return RedirectResponse
     */
    public function clockIn(): RedirectResponse
    {
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', $now->today())
            ->first();

        if (!empty($attendance->clock_in)) {
            return redirect()->route('attendance.index');
        }

        if ($attendance === null) {
            $attendance = Attendance::create([
                'user_id' => auth()->id(),
                'date' => $now->format('Y-m-d'),
            ]);
        }

        $this->authorize('update', $attendance);
        $attendance->update([
            'clock_in' => $now->format('H:i'),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 休憩開始時刻を登録
     *
     * @param  Attendance $attendance 勤怠モデル
     * @return RedirectResponse
     */
    public function breakStart(Attendance $attendance): RedirectResponse
    {
        $breakTime = BreakTime::where('attendance_id', $attendance->id)
            ->where('break_end', null)
            ->first();

        if (empty($attendance->clock_in) || !empty($attendance->clock_out) || !empty($breakTime)) {
            return redirect()->route('attendance.index');
        }

        $this->authorize('update', $attendance);

        $now = Carbon::now();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => $now->format('H:i'),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 休憩終了時刻を登録
     *
     * @param  Attendance $attendance 勤怠モデル
     * @return RedirectResponse
     */
    public function breakEnd(Attendance $attendance): RedirectResponse
    {
        $breakTime = BreakTime::where('attendance_id', $attendance->id)
            ->where('break_end', null)
            ->first();

        if (empty($breakTime)) {
            return redirect()->route('attendance.index');
        }

        $this->authorize('update', $attendance);

        $now = Carbon::now();

        $breakTime->update([
            'break_end' => $now->format('H:i'),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 退勤時刻を登録
     *
     * @param  Attendance $attendance 勤怠モデル
     * @return RedirectResponse
     */
    public function clockOut(Attendance $attendance): RedirectResponse
    {
        $breakTime = BreakTime::where('attendance_id', $attendance->id)
            ->where('break_end', null)
            ->first();

        if (!empty($attendance->clock_out) || !empty($breakTime) || empty($attendance->clock_in)) {
            return redirect()->route('attendance.index');
        }

        $this->authorize('update', $attendance);

        $now = Carbon::now();

        $attendance->update([
            'clock_out' => $now->format('H:i'),
        ]);

        return redirect()->route('attendance.index');
    }
}

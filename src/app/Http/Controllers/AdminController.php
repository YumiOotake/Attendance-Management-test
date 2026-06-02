<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequestStoreRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function attendanceDetail($id, Request $request)
    {
        $monthOffset = $request->query('month');
        $userId = $request->query('user_id');

        if ($id == 0 && !empty($request->date)) {
            $attendance = new Attendance([
                'user_id' => $userId,
                'date' => $request->date,
            ]);
            $attendanceDate = $request->date;
            $attendanceUser = $userId;
        } else {
            $attendance = Attendance::findOrFail($id);
            $attendanceDate = $attendance->date;
            $attendanceUser = $attendance->user_id;
        }

        $attendanceUser = User::where('id', $attendanceUser)->firstOrFail();

        $attendanceRequest = $attendance->exists
            ? AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('status', 1)
            ->first()
            : null;
        $isPending = $attendanceRequest !== null;
        $breaks = $attendance->exists ? $attendance->breakTimes : collect();
        $requestBreaks = $attendanceRequest?->requestBreakTimes ?? collect();

        $from = $request->query('from') ?? null;

        return view('admin.attendance_detail', compact(
            'attendance',
            'attendanceRequest',
            'isPending',
            'breaks',
            'requestBreaks',
            'attendanceDate',
            'attendanceUser',
            'from',
            'monthOffset',
        ));
    }

    public function attendanceRequest($id, AttendanceRequestStoreRequest $request)
    {
        $clock_in = $request->requested_clock_in ? Carbon::createFromFormat('H:i', $request->requested_clock_in) : null;
        $clock_out = $request->requested_clock_out ? Carbon::createFromFormat('H:i', $request->requested_clock_out) : null;

        $userId = $request->query('user_id');

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $userId, 'date' => $request->date],
            ['clock_in' => $clock_in, 'clock_out' => $clock_out]
        );

        $break_starts = $request->requested_break_start;
        $break_ends = $request->requested_break_end;

        // 既存の休憩を削除してから作り直す
        $attendance->breakTimes()->delete();

        foreach ($break_starts as $key => $break_start) {
            if (empty($break_start) && empty($break_ends[$key])) {
                continue;
            }
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => Carbon::createFromFormat('H:i', $break_start),
                'break_end' => Carbon::createFromFormat('H:i', $break_ends[$key]),
            ]);
        }

        $from = $request->query('from');
        $monthOffset = $request->query('month');
        return $from === 'staff' ? redirect()->route('admin.attendance.staff', [
            'id' => $userId,
            'month' => $monthOffset
        ]) : redirect()->route('admin.list');
    }

    public function attendanceList(Request $request)
    {
        $dateOffset = $request->query('date', 0);
        $targetDate = Carbon::now()->addDays($dateOffset);

        $attendances = Attendance::where('date', $targetDate->format('Y-m-d'))
            ->with(['user', 'breakTimes'])
            ->get();

        return view('admin.attendance_list', compact('targetDate', 'dateOffset', 'attendances'));
    }

    public function staffList()
    {
        $users = User::where('admin_status', '!=', true)
            ->get();

        return view('admin.staff_list', compact('users'));
    }

    public function staffAttendanceList($id, Request $request)
    {
        $user = User::where('id', $id)->firstOrFail();
        $monthOffset = $request->query('month', 0);

        $targetMonth = Carbon::now()->addMonths($monthOffset);

        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();

        $dateLists = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendanceByDate = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('breakTimes')
            ->get()
            ->keyBy(fn($a) => $a->date->format('Y-m-d'));


        return view('admin.attendance_staff', compact('targetMonth', 'monthOffset', 'dateLists', 'attendanceByDate', 'user'));
    }
}

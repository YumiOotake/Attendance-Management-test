<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequestStoreRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\RequestBreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceDetailController extends Controller
{
    /**
     * 勤怠一覧画面を表示
     *
     * @param Request $request リクエスト（月オフセット: month）
     * @return View
     */
    public function list(Request $request): View
    {
        $user = auth()->user();
        $monthOffset = $request->query('month', 0);

        $targetMonth = Carbon::now()->addMonthNoOverflow($monthOffset);

        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();

        $dateLists = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendanceByDate = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('breakTimes')
            ->get()
            ->keyBy(fn($a) => $a->date->format('Y-m-d'));

        return view('attendance.list', compact('targetMonth', 'monthOffset', 'dateLists', 'attendanceByDate'));
    }

    /**
     * 申請一覧画面を表示
     *
     * @param Request $request リクエスト（tab）
     * @return View
     */
    public function requestList(Request $request): View
    {
        $tab = $request->query('tab');
        $user = auth()->user();

        if (auth()->user()->admin_status) {
            if ($tab === 'pending' || empty($tab)) {
                $attendanceRequests = AttendanceRequest::where('status', 1)
                    ->with(['attendance.user'])
                    ->get();
            } else {
                $attendanceRequests = AttendanceRequest::where('status', 2)
                    ->with(['attendance.user'])
                    ->get();
            }
            return view('attendance.stamp_correction_request', compact('attendanceRequests'));
        }

        if ($tab === 'pending' || empty($tab)) {
            $attendanceRequests = AttendanceRequest::where('user_id', $user->id)
                ->where('status', 1)
                ->with(['attendance.user'])
                ->get();
        } else {
            $attendanceRequests = AttendanceRequest::where('user_id', $user->id)
                ->where('status', 2)
                ->with(['attendance.user'])
                ->get();
        }

        return view('attendance.stamp_correction_request', compact('attendanceRequests'));
    }

    /**
     * 勤怠詳細画面を表示
     *
     * @param int $id 勤怠レコード
     * @param Request $request リクエスト（日付）
     * @return View
     */
    public function show(int $id, Request $request): View
    {
        if ($id == 0 && !empty($request->date)) {
            $attendance = new Attendance([
                'user_id' => auth()->id(),
                'date' => $request->date,
            ]);
        } else {
            $attendance = Attendance::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        $this->authorize('view', $attendance);

        $attendanceRequest = $attendance->exists
            ? AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('status', 1)
            ->first()
            : null;
        $isPending = $attendanceRequest !== null;
        $breaks = $attendance->exists ? $attendance->breakTimes : collect();
        $requestBreaks = $attendanceRequest?->requestBreakTimes ?? collect();

        return view('attendance.detail', compact(
            'attendance',
            'attendanceRequest',
            'isPending',
            'breaks',
            'requestBreaks'
        ));
    }

    /**
     * 勤怠修正の申請
     *
     * @param int $id 勤怠レコード
     * @param AttendanceRequestStoreRequest $request リクエスト（修正時刻）
     * @return RedirectResponse
     */
    public function request(int $id, AttendanceRequestStoreRequest $request): RedirectResponse
    {
        $user = auth()->user();

        if ($id == 0 && !empty($request->date)) {
            $attendance = Attendance::firstOrCreate([
                'user_id' => auth()->id(),
                'date' => $request->date,
            ]);
        } else {
            $attendance = Attendance::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        $clockIn = $request->requested_clock_in ? Carbon::createFromFormat('H:i', $request->requested_clock_in) : null;
        $clockOut = $request->requested_clock_out ? Carbon::createFromFormat('H:i', $request->requested_clock_out) : null;

        $hasPendingRequest = AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->exists();
        if ($hasPendingRequest) {
            return redirect()->route('attendance.request.list');
        }

        $this->authorize('create', [AttendanceRequest::class, $attendance]);

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => $clockIn,
            'requested_clock_out' => $clockOut,
            'note' => $request->note,
            'status' => 1,
        ]);

        $breakStarts = $request->requested_break_start ?? [];
        $breakEnds = $request->requested_break_end ?? [];

        foreach ($breakStarts as $key => $breakStart) {
            if (empty($breakStart) && empty($breakEnds[$key])) {
                continue;
            }
            $breakStart = Carbon::createFromFormat('H:i', $breakStart);
            $breakEnd = Carbon::createFromFormat('H:i', $breakEnds[$key]);
            RequestBreakTime::create([
                'attendance_request_id' => $attendanceRequest->id,
                'requested_break_start' => $breakStart,
                'requested_break_end' => $breakEnd,
            ]);
        }

        return redirect()->route('attendance.request.list');
    }
}

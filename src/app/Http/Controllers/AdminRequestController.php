<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminRequestController extends Controller
{
    /**
     * 修正申請承認画面を表示
     *
     * @param int $attendance_correct_request_id 勤怠申請レコード
     * @return View
     */
    public function requestApproveShow(int $attendance_correct_request_id): View
    {
        $attendanceRequest = AttendanceRequest::where('id', $attendance_correct_request_id)->firstOrFail();

        $requestBreaks = $attendanceRequest->requestBreakTimes;

        $isPending = $attendanceRequest->status !== 2;

        return view('admin.stamp_correction_request_approve', compact(
            'attendanceRequest',
            'requestBreaks',
            'isPending'
        ));
    }

    /**
     * 修正申請を実行
     *
     * @param int $attendance_correct_request_id 勤怠申請レコード
     * @return RedirectResponse
     */
    public function requestApprove(int $attendance_correct_request_id): RedirectResponse
    {
        $attendanceRequest = AttendanceRequest::where('id', $attendance_correct_request_id)->firstOrFail();

        if ($attendanceRequest->status === 2) {
            return redirect()->route('admin.request.approve.show', compact('attendance_correct_request_id'));
        }

        DB::transaction(function () use ($attendanceRequest) {

            $attendanceRequest->update([
                'status' => 2,
            ]);

            $attendanceRequest->attendance->update([
                'clock_in' => $attendanceRequest->requested_clock_in,
                'clock_out' => $attendanceRequest->requested_clock_out,
            ]);

            $attendanceRequest->attendance->breakTimes()->delete();
            foreach ($attendanceRequest->requestBreakTimes as $requestBreak) {
                BreakTime::create([
                    'attendance_id' => $attendanceRequest->attendance_id,
                    'break_start' => $requestBreak->requested_break_start,
                    'break_end' => $requestBreak->requested_break_end,
                ]);
            }
        });

        return redirect()->route('admin.request.approve.show', compact('attendance_correct_request_id'));
    }
}

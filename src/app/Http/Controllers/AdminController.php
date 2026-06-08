<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequestStoreRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    /**
     * 勤怠一覧画面を表示
     *
     * @return View
     */
    public function showLoginForm(): View
    {
        return view('admin.login');
    }

    /**
     * 勤怠詳細画面を表示
     *
     * @param int $id 勤怠レコード
     * @param Request $request リクエスト（月オフセット: month）
     * @return View
     */
    public function attendanceDetail(int $id, Request $request): View
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

    /**
     * 勤怠を修正
     *
     * @param int $id 勤怠申請レコード
     * @param Request $request リクエスト（勤怠修正）
     * @return RedirectResponse
     */
    public function attendanceRequest(int $id, AttendanceRequestStoreRequest $request): RedirectResponse
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

    /**
     * 勤怠一覧画面を表示
     *
     * @param Request $request リクエスト（日付）
     * @return View
     */
    public function attendanceList(Request $request): View
    {
        $dateOffset = $request->query('date', 0);
        $targetDate = Carbon::now()->addDays($dateOffset);

        $attendances = Attendance::where('date', $targetDate->format('Y-m-d'))
            ->with(['user', 'breakTimes'])
            ->get();

        return view('admin.attendance_list', compact('targetDate', 'dateOffset', 'attendances'));
    }

    /**
     * スタッフ一覧画面を表示
     *
     * @return View
     */
    public function staffList(): View
    {
        $users = User::where('admin_status', '!=', true)
            ->get();

        return view('admin.staff_list', compact('users'));
    }

    /**
     * スタッフ別勤怠一覧画面を表示
     *
     * @param int $id 勤怠申請レコード
     * @param Request $request リクエスト（月オフセット: month）
     * @return View
     */
    public function staffAttendanceList(int $id, Request $request): View
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

    /**
     * CSV出力
     *
     * @param Request $request リクエスト（ユーザーID: id、月オフセット: month）
     * @return StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        $user = User::where('id', $request->query('id'))->firstOrFail();
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

        $attendances = [];
        foreach ($dateLists as $dateList) {
            $attendances[] = $attendanceByDate[$dateList->format('Y-m-d')] ?? null;
        }

        $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];

        $response = new StreamedResponse(function () use ($attendances, $csvHeader, $dateLists) {

            $handle = fopen('php://output', 'w');

            fprintf($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $csvHeader);

            foreach ($dateLists as $key => $dateList) {
                $attendance = $attendances[$key] ?? null;
                fputcsv($handle, [
                    $dateList->format('Y/m/d'),
                    $attendance->formatted_clock_in ?? '',
                    $attendance->formatted_clock_out ?? '',
                    $attendance->total_break_time ?? '',
                    $attendance->total_work_time ?? '',
                ]);
            }

            fclose($handle);
        });

        $filename = $user->name . '_' . $targetMonth->format('Y-m') . '.csv';
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}

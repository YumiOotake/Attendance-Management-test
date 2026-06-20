<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Collection;

class AttendanceRecordController extends Controller
{
    /**
     * マイ勤怠レポート画面を表示
     *
     * @return View マイ勤怠レポート画面
     */
    public function index(): View
    {
        $user = auth()->user();
        $attendances = $this->getReportAttendances($user->id);
        $dailyStats = $this->buildDailyStats($attendances);
        $summary = $this->buildSummary($dailyStats);
        $monthlyTrend = $this->buildMonthlyTrend($dailyStats);
        $anomalies = $this->buildAnomalies($dailyStats);

        return view('attendance.report', compact(
            'summary',
            'monthlyTrend',
            'anomalies',
        ));
    }

    /**
     * 指定したユーザーの過去６ヶ月分の勤怠を取得する
     *
     * @param int $userId ログインユーザーID
     * @return Collection 勤怠コレクション
     */
    private function getReportAttendances(int $userId): Collection
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->with('breakTimes')
            ->get();

        return $attendances;
    }

    /**
     * 勤怠コレクションを表示項目ごとの連想配列に変換
     *
     * @param Collection $attendances 勤怠コレクション
     * @return Collection 連想配列にした勤怠コレクション
     */
    private function buildDailyStats(Collection $attendances): Collection
    {
        $dailyStats = $attendances->map(function ($attendance) {
            $workMinutes = $attendance->getTotalWorkMinutes();
            $overtimeMinutes = max($workMinutes - 480, 0);

            $startTime = Carbon::createFromFormat('H:i', '09:00');
            $endTime = Carbon::createFromFormat('H:i', '18:00');

            $isLate = $attendance->clock_in
                ? Carbon::parse($attendance->clock_in)->gt($startTime)
                : false;

            $isEarlyLeave = $attendance->clock_out
                ? Carbon::parse($attendance->clock_out)->lt($endTime)
                : false;

            $isLongWork = $workMinutes > 600;

            return [
                'date' => $attendance->date,
                'work_minutes' => $workMinutes,
                'overtime_minutes' => $overtimeMinutes,
                'late_count' => $isLate ? 1 : 0,
                'early_leave_count' => $isEarlyLeave ? 1 : 0,
                'long_work_count' => $isLongWork ? 1 : 0,
            ];
        });

        return $dailyStats;
    }

    /**
     * 日次集計データから基本サマリー用の配列を作成する
     *
     * @param Collection $dailyStats 日次集計データ
     * @return array 基本サマリー表示用配列
     */
    private function buildSummary(Collection $dailyStats): array
    {
        $totalWorkMinutes = $dailyStats->sum('work_minutes');
        $totalOvertimeMinutes = $dailyStats->sum('overtime_minutes');
        $averageWorkMinutes = $dailyStats->count() > 0 ? (int) floor($totalWorkMinutes / $dailyStats->count()) : 0;

        return [
            'total_work_time' => $this->formatMinutes($totalWorkMinutes),
            'total_overtime_time' => $this->formatMinutes($totalOvertimeMinutes),
            'average_work_time' => $this->formatMinutes($averageWorkMinutes),
        ];
    }

    /**
     * 日次集計データを月ごとに集計し、月次推移用の配列を作成する
     *
     * @param Collection $dailyStats 日次集計データ
     * @return Collection 月次推移表示用コレクション
     */
    private function buildMonthlyTrend(Collection $dailyStats): Collection
    {
        $months = collect(range(0, 5))
            ->map(fn($i) => Carbon::now()->subMonths(5 - $i)->format('Y-m'));

        $monthlyStats = $dailyStats->groupBy(fn($item) => $item['date']->format('Y-m'))
            ->map(function ($items, $month) {
                return [
                    'month' => $month,
                    'work_minutes' => $items->sum('work_minutes'),
                    'overtime_minutes' => $items->sum('overtime_minutes'),
                ];
            });

        return $months->map(function ($month) use ($monthlyStats) {
            $stat = $monthlyStats->get($month);
            $workMinutes = $stat['work_minutes'] ?? 0;
            $overtimeMinutes = $stat['overtime_minutes'] ?? 0;
            return [
                'month' => $month,
                'work_minutes' => $workMinutes,
                'overtime_minutes' => $overtimeMinutes,
                'work_time_label' => $this->formatMinutes($workMinutes),
                'overtime_time_label' => $this->formatMinutes($overtimeMinutes),
            ];
        });
    }

    /**
     * 今月分の日次集計データから異常検知回数を集計する
     *
     * @param Collection $dailyStats 日次集計データ
     * @return array 異常検知表示用配列
     */
    private function buildAnomalies(Collection $dailyStats): array
    {
        $currentMonthStats = $dailyStats->filter(function ($item) {
            return Carbon::parse($item['date'])->isCurrentMonth();
        });

        $lateCount = $currentMonthStats->sum('late_count');
        $earlyLeaveCount = $currentMonthStats->sum('early_leave_count');
        $longWorkCount = $currentMonthStats->sum('long_work_count');

        return [
            'late_count' => $lateCount,
            'early_leave_count' => $earlyLeaveCount,
            'long_work_count' => $longWorkCount,
        ];
    }

    /**
     * 分単位の時間を表示用文字列に変換する
     *
     * @param int $minutes 分単位の時間
     * @return string 表示用文字列
     */
    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $minutes = $minutes % 60;

        return "{$hours}h {$minutes}m";
    }
}

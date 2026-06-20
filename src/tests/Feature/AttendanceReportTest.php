<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //勤怠レポート--ゲストはアクセス不可
    /** @test */
    public function test_guest_cannot_access_report_page(): void
    {
        $response = $this->get(route('attendance.report'));

        $response->assertRedirect(route('login'));
    }

    //勤怠レポート--計算
    /** @test */
    public function test_authenticated_user_statistics_are_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $date = Carbon::now()->firstOfMonth();

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => $date->copy()->addDay()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '21:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
        $attendance3 = Attendance::create([
            'user_id' => $user->id,
            'date' => $date->copy()->addDays(2)->format('Y-m-d'),
            'clock_in' => '10:00:00',
            'clock_out' => '17:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance3->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.report'));

        $response->assertStatus(200);
        $response->assertViewHas('summary', function ($summary) {
            return $summary['total_work_time'] === '25h 0m'
                && $summary['total_overtime_time'] === '3h 0m'
                && $summary['average_work_time'] === '8h 20m';
        });

        $response->assertViewHas('anomalies', function ($anomalies) {
            return $anomalies['late_count'] === 1
                && $anomalies['early_leave_count'] === 1
                && $anomalies['long_work_count'] === 1;
        });

        $response->assertViewHas('monthlyTrend', function ($monthlyTrend) {
            $currentMonth = now()->format('Y-m');
            $current = $monthlyTrend->firstWhere('month', $currentMonth);

            return $monthlyTrend->count() === 6
                && $current['work_time_label'] === '25h 0m'
                && $current['overtime_time_label'] === '3h 0m';
        });
    }

    //勤怠レポート--勤怠なし
    /** @test */
    public function test_report_handles_user_with_no_attendance_records_safely(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.report'));

        $response->assertStatus(200);
        $response->assertViewHas('summary', function ($summary) {
            return $summary['total_work_time'] === '0h 0m'
                && $summary['total_overtime_time'] === '0h 0m'
                && $summary['average_work_time'] === '0h 0m';
        });

        $response->assertViewHas('anomalies', function ($anomalies) {
            return $anomalies['late_count'] === 0
                && $anomalies['early_leave_count'] === 0
                && $anomalies['long_work_count'] === 0;
        });

        $response->assertViewHas('monthlyTrend', function ($monthlyTrend) {
            $currentMonth = now()->format('Y-m');
            $current = $monthlyTrend->firstWhere('month', $currentMonth);

            return $monthlyTrend->count() === 6
                && $current['work_time_label'] === '0h 0m'
                && $current['overtime_time_label'] === '0h 0m';
        });
    }
}

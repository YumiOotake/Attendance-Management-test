<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //勤怠一覧--自分の勤怠が全て表示
    /** @test */
    public function test_attendance_list_displays_all_user_attendances(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $firstDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $secondDate = Carbon::now()->startOfMonth()->addDay()->format('Y-m-d');

        Attendance::create([
            'user_id' => $user->id,
            'date' => $firstDate,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $secondDate,
            'clock_in' => '09:30:00',
            'clock_out' => '18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'date' => $firstDate,
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertViewHas('attendanceByDate', function ($attendanceByDate) use ($user, $firstDate, $secondDate) {
            return $attendanceByDate->has($firstDate)
                && $attendanceByDate->has($secondDate)
                && $attendanceByDate->get($firstDate)->user_id === $user->id
                && $attendanceByDate->get($secondDate)->user_id === $user->id;
        });
    }

    //勤怠一覧--現在の月表示
    /** @test */
    public function test_attendance_list_displays_current_month(): void
    {
        $user = User::factory()->create();

        $month = Carbon::now()->format('Y/m');

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee($month);
    }

    //勤怠一覧--前月ボタン
    /** @test */
    public function test_attendance_list_displays_previous_month_when_prev_clicked(): void
    {
        $user = User::factory()->create();

        $previousMonthDate = Carbon::now()->startOfMonth()->subMonthNoOverflow()->format('Y-m-d');
        $currentMonthDate = Carbon::now()->startOfMonth()->format('Y-m-d');

        Attendance::create([
            'user_id' => $user->id,
            'date' => $previousMonthDate,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $currentMonthDate,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => -1]));

        $response->assertStatus(200);
        $response->assertViewHas('attendanceByDate', function ($attendanceByDate) use ($previousMonthDate, $currentMonthDate, $user) {
            return $attendanceByDate->has($previousMonthDate)
                && $attendanceByDate->get($previousMonthDate)->user_id === $user->id
                && !$attendanceByDate->has($currentMonthDate);
        });
    }

    //勤怠一覧--翌月ボタン
    /** @test */
    public function test_attendance_list_displays_next_month_when_next_clicked(): void
    {
        $user = User::factory()->create();

        $nextMonthDate = Carbon::now()->startOfMonth()->addMonthNoOverflow()->format('Y-m-d');
        $currentMonthDate = Carbon::now()->startOfMonth()->format('Y-m-d');

        Attendance::create([
            'user_id' => $user->id,
            'date' => $nextMonthDate,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $currentMonthDate,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' =>
        +1]));

        $response->assertStatus(200);
        $response->assertViewHas('attendanceByDate', function ($attendanceByDate) use ($nextMonthDate, $currentMonthDate, $user) {
            return $attendanceByDate->has($nextMonthDate)
                && $attendanceByDate->get($nextMonthDate)->user_id === $user->id
                && !$attendanceByDate->has($currentMonthDate);
        });
    }

    //勤怠一覧--詳細ボタン
    /** @test */
    public function test_detail_button_navigates_to_attendance_detail_page(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.show', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($viewAttendance) use ($user, $attendance) {
            return $viewAttendance->id === $attendance->id
                && $viewAttendance->user_id === $user->id
                && $viewAttendance->date->format('Y-m-d') === Carbon::now()->format('Y-m-d')
                && $viewAttendance->clock_in === '09:00:00'
                && $viewAttendance->clock_out === '18:00:00';
        });
    }

    //勤怠詳細--名前表示
    /** @test */
    public function test_attendance_detail_displays_login_user_name(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($viewAttendance) use ($user) {
            return $viewAttendance->user->name === $user->name;
        });
    }

    //勤怠詳細--日付表示
    /** @test */
    public function test_attendance_detail_displays_selected_date(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($viewAttendance) use ($attendance) {
            return $viewAttendance->date->format('Y-m-d') === $attendance->date->format('Y-m-d');
        });
    }

    //勤怠詳細--出勤退勤時刻
    /** @test */
    public function test_attendance_detail_displays_correct_clock_times(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($viewAttendance) use ($attendance) {
            return $viewAttendance->clock_in === $attendance->clock_in
                && $viewAttendance->clock_out === $attendance->clock_out;
        });
    }

    //勤怠詳細--休憩時刻
    /** @test */
    public function test_attendance_detail_displays_correct_break_times(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('breaks', function ($viewBreaks) use ($breakTime) {
            return $viewBreaks->contains(function ($break) use ($breakTime) {
                return $break->break_start === $breakTime->break_start
                    && $break->break_end === $breakTime->break_end;
            });
        });
    }
}

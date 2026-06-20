<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //出勤機能--出勤ボタン
    /** @test */
    public function test_clock_in(): void
    {
        $user = User::find(2);
        $now = Carbon::now();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->today(),
            'clock_in' => null,
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('出勤');

        $response = $this->actingAs($user)->patch(route('attendance.clock-in'));
        $response->assertRedirect(route('attendance.index'));

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('出勤中');
    }

    //出勤機能--1日1回
    /** @test */
    public function test_clock_in_once_daily(): void
    {
        $user = User::find(2);
        $now = Carbon::now();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->setHour(19),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertDontSee('出勤');
    }

    //出勤機能--勤怠一覧で確認
    /** @test */
    public function test_attendance_list_displays_clock_in_time(): void
    {
        Carbon::setTestNow('2030-01-10 09:00:00');

        $now = Carbon::now();
        $user = User::find(2);
        Attendance::create([
            'user_id' => $user->id,
            'date' => $now,
            'clock_in' => null,
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->patch(route('attendance.clock-in'));

        $response = $this->actingAs($user)->get(route('attendance.list'));
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }

    //休憩機能--休憩入ボタン
    /** @test */
    public function test_break_start(): void
    {
        $user = User::find(2);
        $now = Carbon::now();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $now->today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('休憩入');

        $response = $this->actingAs($user)->patch(route('attendance.break-start', $attendance));
        $response->assertRedirect(route('attendance.index'));

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('休憩中');
    }

    //休憩機能--休憩入は1日何回も休憩
    /** @test */
    public function test_break_start_multiple_times(): void
    {
        $user = User::find(2);
        $now = Carbon::now();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $now->today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->patch(route('attendance.break-start', $attendance));
        $response = $this->actingAs($user)->patch(route('attendance.break-end', $attendance));

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('休憩入');
    }

    //休憩機能--休憩戻ボタン
    /** @test */
    public function test_break_end(): void
    {
        $user = User::find(2);
        $now = Carbon::now();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $now->today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->patch(route('attendance.break-start', $attendance));
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('休憩戻');

        $response = $this->actingAs($user)->patch(route('attendance.break-end', $attendance));
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('出勤中');
    }

    //休憩機能--休憩戻は1日何回も休憩
    /** @test */
    public function test_break_end_multiple_times(): void
    {
        $user = User::find(2);
        $now = Carbon::now();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $now->today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->patch(route('attendance.break-start', $attendance));
        $response = $this->actingAs($user)->patch(route('attendance.break-end', $attendance));
        $response = $this->actingAs($user)->patch(route('attendance.break-start', $attendance));

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('休憩戻');
    }

    //休憩機能--勤怠一覧で確認
    /** @test */
    public function test_attendance_list_displays_break_time(): void
    {
        Carbon::setTestNow('2030-01-10 12:00:00');

        $user = User::find(2);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->patch(route('attendance.break-start', $attendance));

        Carbon::setTestNow('2030-01-10 13:00:00');
        $response = $this->actingAs($user)->patch(route('attendance.break-end', $attendance));

        $response = $this->actingAs($user)->get(route('attendance.list'));
        $response->assertSee('1:00');

        Carbon::setTestNow();
    }

    //退勤機能--退勤ボタン
    /** @test */
    public function test_clock_out(): void
    {
        $user = User::find(2);
        Carbon::setTestNow('2030-01-10 09:00:00');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('退勤');

        Carbon::setTestNow('2030-01-10 18:00:00');
        $response = $this->actingAs($user)->patch(route('attendance.clock-out', $attendance));
        $response->assertRedirect(route('attendance.index'));

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }

    //退勤機能--勤怠一覧で確認
    /** @test */
    public function test_attendance_list_displays_clock_out_time(): void
    {
        Carbon::setTestNow('2030-01-10 09:00:00');

        $user = User::find(2);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now(),
            'clock_in' => null,
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->patch(route('attendance.clock-in'));

        Carbon::setTestNow('2030-01-10 18:00:00');
        $response = $this->actingAs($user)->patch(route('attendance.clock-out', $attendance));

        $response = $this->actingAs($user)->get(route('attendance.list'));
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}

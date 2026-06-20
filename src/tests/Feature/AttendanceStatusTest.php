<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //日時取得
    /** @test */
    public function test_get_date(): void
    {
        $user = User::find(2);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $now = Carbon::now();
        $nowDate = $now->isoFormat('YYYY年M月D日(ddd)');
        $nowTime = $now->format('H:i');

        $response->assertStatus(200);
        $response->assertSee($nowDate);
        $response->assertSee($nowTime);
    }

    //勤怠ステータス--勤務外
    /** @test */
    public function test_get_status_none(): void
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

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    //勤怠ステータス--出勤中
    /** @test */
    public function test_get_status_working(): void
    {
        $user = User::find(2);
        $now = Carbon::now();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->setHour(10),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    //勤怠ステータス--休憩中
    /** @test */
    public function test_get_status_break(): void
    {
        $user = User::find(2);
        $now = Carbon::now();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $now->setHour(13),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    //勤怠ステータス--退勤済
    /** @test */
    public function test_get_status_done(): void
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

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}

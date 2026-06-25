<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function createDate(): array
    {
        $adminUser = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();
        $date = Carbon::now()->format('Y-m-d');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        return compact('adminUser', 'user', 'date', 'attendance');
    }

    //勤怠一覧--情報取得
    /** @test */
    public function test_admin_attendance_list_displays_all_users_attendances(): void
    {
        $data = $this->createDate();
        $adminUser = $data['adminUser'];
        $date = $data['date'];

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
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
            'user_id' => $user2->id,
            'date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.list'));

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y/m/d'));
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    //勤怠一覧--日付
    /** @test */
    public function test_admin_attendance_list_displays_current_date(): void
    {
        $data = $this->createDate();
        $adminUser = $data['adminUser'];

        $response = $this->actingAs($adminUser)->get(route('admin.list'));

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y/m/d'));
    }

    //勤怠一覧--前日ボタン
    /** @test */
    public function test_admin_attendance_list_displays_previous_day_when_prev_clicked(): void
    {
        Carbon::setTestNow('2030-01-10 09:00:00');

        $adminUser = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create();

        $baseDate = Carbon::now();
        $previousDate = $baseDate->copy()->subDay()->format('Y-m-d');
        $currentDate = $baseDate->copy()->format('Y-m-d');

        Attendance::create([
            'user_id' => $user->id,
            'date' => $previousDate,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $currentDate,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.list', [
            'date' => -1,
        ]));

        $response->assertStatus(200);
        $response->assertSee($baseDate->subDay()->format('Y/m/d'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');

        Carbon::setTestNow();
    }

    //勤怠一覧--翌日ボタン
    /** @test */
    public function test_admin_attendance_list_displays_next_day_when_next_clicked(): void
    {

        Carbon::setTestNow('2030-01-10 09:00:00');

        $adminUser = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create();

        $baseDate = Carbon::now();
        $nextDate = $baseDate->copy()->addDay()->format('Y-m-d');
        $currentDate = $baseDate->copy()->format('Y-m-d');

        Attendance::create([
            'user_id' => $user->id,
            'date' => $nextDate,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $currentDate,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.list', [
            'date' => +1,
        ]));

        $response->assertStatus(200);
        $response->assertSee($baseDate->addDay()->format('Y/m/d'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');

        Carbon::setTestNow();
    }

    //勤怠詳細--情報取得
    /** @test */
    public function test_admin_attendance_detail_displays_selected_data(): void
    {
        $data = $this->createDate();
        $adminUser = $data['adminUser'];
        $date = $data['date'];

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
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
            'user_id' => $user2->id,
            'date' => $date,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);
        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => '14:00:00',
            'break_end' => '15:00:00',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.detail', $attendance1));

        $response->assertStatus(200);
        $response->assertSee($attendance1->date->format('Y年'));
        $response->assertSee($attendance1->date->format('n月j日'));
        $response->assertSee($user1->name);
        $response->assertDontSee($user2->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    //勤怠修正--出勤時刻バリデーション
    /** @test */
    public function test_admin_error_message_displays_when_clock_in_is_after_clock_out(): void
    {
        $data = $this->createDate();
        $adminUser = $data['adminUser'];
        $attendance = $data['attendance'];

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.detail', $attendance));
        $response->assertStatus(200);

        $response = $this->actingAs($adminUser)->patch(route('admin.request', $attendance), [
            'requested_clock_in' => '19:00:00',
            'requested_clock_out' => '18:00:00',
        ]);

        $response->assertSessionHasErrors(['requested_clock_in' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    //勤怠修正--休憩開始時刻バリデーション
    /** @test */
    public function test_admin_error_message_displays_when_break_start_is_after_clock_out(): void
    {
        $data = $this->createDate();
        $adminUser = $data['adminUser'];
        $attendance = $data['attendance'];

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.detail', $attendance));
        $response->assertStatus(200);

        $response = $this->actingAs($adminUser)->patch(route('admin.request', $attendance), [
            'requested_clock_in' => '19:00:00',
            'requested_clock_out' => '18:00:00',
            'requested_break_start' => ['14:00'],
            'requested_break_end' => ['13:00'],
            'note' => '体調不良のため',
        ]);

        $response->assertSessionHasErrors(['requested_break_start.0' => '休憩時間が不適切な値です']);
    }

    //勤怠修正--休憩終了時刻バリデーション
    /** @test */
    public function test_admin_error_message_displays_when_break_end_is_after_clock_out(): void
    {
        $data = $this->createDate();
        $adminUser = $data['adminUser'];
        $attendance = $data['attendance'];

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.detail', $attendance));
        $response->assertStatus(200);

        $response = $this->actingAs($adminUser)->patch(route('admin.request', $attendance), [
            'requested_clock_in' => '19:00:00',
            'requested_clock_out' => '18:00:00',
            'requested_break_start' => ['12:00'],
            'requested_break_end' => ['19:00'],
            'note' => '体調不良のため',
        ]);

        $response->assertSessionHasErrors(['requested_break_end.0' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    //勤怠修正--備考バリデーション
    /** @test */
    public function test_admin_error_message_displays_when_note_is_empty(): void
    {
        $data = $this->createDate();
        $adminUser = $data['adminUser'];
        $attendance = $data['attendance'];

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.detail', $attendance));
        $response->assertStatus(200);

        $response = $this->actingAs($adminUser)->patch(route('admin.request', $attendance), [
            'requested_clock_in' => '19:00:00',
            'requested_clock_out' => '18:00:00',
            'requested_break_start' => ['12:00'],
            'requested_break_end' => ['13:00'],
            'note' => '',
        ]);

        $response->assertSessionHasErrors(['note' => '備考を記入してください']);
    }
}

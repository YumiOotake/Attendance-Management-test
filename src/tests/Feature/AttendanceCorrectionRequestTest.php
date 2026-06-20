<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Carbon;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function createDate(): array
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        return [$user, $attendance];
    }

    //勤怠修正--出勤時刻バリデーション
    /** @test */
    public function test_error_message_displays_when_clock_in_is_after_clock_out(): void
    {
        [$user, $attendance] = $this->createDate();

        $response = $this->actingAs($user)->get(route('attendance.show', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post(route('attendance.request', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]), [
            'requested_clock_in' => '19:00:00',
            'requested_clock_out' => '18:00:00',
        ]);

        $response->assertSessionHasErrors(['requested_clock_in' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    //勤怠修正--休憩開始時刻バリデーション
    /** @test */
    public function test_error_message_displays_when_break_start_is_after_clock_out(): void
    {
        [$user, $attendance] = $this->createDate();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post(route('attendance.request', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'requested_break_start' => ['14:00'],
            'requested_break_end' => ['13:00'],
            'note' => '体調不良のため',
        ]);

        $response->assertSessionHasErrors(['requested_break_start.0' => '休憩時間が不適切な値です']);
    }

    //勤怠修正--休憩終了時刻バリデーション
    /** @test */
    public function test_error_message_displays_when_break_end_is_after_clock_out(): void
    {
        [$user, $attendance] = $this->createDate();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post(route('attendance.request', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'requested_break_start' => ['14:00'],
            'requested_break_end' => ['19:00'],
            'note' => '体調不良のため',
        ]);

        $response->assertSessionHasErrors(['requested_break_end.0' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    //勤怠修正--備考バリデーション
    /** @test */
    public function test_error_message_displays_when_note_is_empty(): void
    {
        [$user, $attendance] = $this->createDate();

        $response = $this->actingAs($user)->get(route('attendance.show', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post(route('attendance.request', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d')
        ]), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '19:00',
            'note' => '',
        ]);

        $response->assertSessionHasErrors(['note' => '備考を記入してください']);
    }

    //勤怠修正--申請
    /** @test */
    public function test_attendance_request_is_created_successfully(): void
    {
        [$user, $attendance] = $this->createDate();

        $adminUser = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($user)->post(route('attendance.request', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d'),
        ]), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '19:00',
            'requested_break_start' => [''],
            'requested_break_end' => [''],
            'note' => '残業のため',
        ]);

        $response->assertRedirect(route('attendance.request.list'));

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'note' => '残業のため',
            'status' => 1,
        ]);

        $response = $this->actingAs($adminUser)->get(route('attendance.request.list'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('残業のため');
        $response->assertSee($attendance->date->format('Y/m/d'));

        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)->firstOrFail();

        $response = $this->actingAs($adminUser)->get(route('admin.request.approve.show', [
            'attendance_correct_request_id' => $attendanceRequest->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('attendanceRequest', function ($viewAttendanceRequest) use ($attendanceRequest) {
            return $viewAttendanceRequest->id === $attendanceRequest->id;
        });
        $response->assertSee('残業のため');
    }

    //勤怠修正--承認待ち
    /** @test */
    public function test_pending_requests_displays_all_user_requests(): void
    {
        [$user, $attendance] = $this->createDate();

        $response = $this->actingAs($user)->post(route('attendance.request', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d'),
        ]), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '19:00',
            'requested_break_start' => [''],
            'requested_break_end' => [''],
            'note' => '残業のため',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->addDay(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        $response = $this->actingAs($user)->post(route('attendance.request', [
            'id' => $attendance2->id,
            'date' => $attendance2->date->format('Y-m-d'),
        ]), [
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '18:00',
            'requested_break_start' => [''],
            'requested_break_end' => [''],
            'note' => '遅刻のため',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.request.list'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('残業のため');
        $response->assertSee('遅刻のため');
        $response->assertSee($attendance->date->format('Y/m/d'));
        $response->assertSee($attendance2->date->format('Y/m/d'));
    }

    //勤怠修正--承認済み
    /** @test */
    public function test_approved_requests_displays_all_approved_requests(): void
    {
        [$user, $attendance] = $this->createDate();

        $response = $this->actingAs($user)->post(route('attendance.request', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d'),
        ]), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '19:00',
            'requested_break_start' => [''],
            'requested_break_end' => [''],
            'note' => '残業のため',
        ]);
        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->addDay(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        $response = $this->actingAs($user)->post(route('attendance.request', [
            'id' => $attendance2->id,
            'date' => $attendance2->date->format('Y-m-d'),
        ]), [
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '18:00',
            'requested_break_start' => [''],
            'requested_break_end' => [''],
            'note' => '遅刻のため',
        ]);

        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)->firstOrFail();
        $attendanceRequest2 = AttendanceRequest::where('attendance_id', $attendance2->id)->firstOrFail();

        $adminUser = User::factory()->create([
            'admin_status' => true,
        ]);

        $this->actingAs($adminUser)->patch(route('admin.request.approve', [
            'attendance_correct_request_id' => $attendanceRequest->id,
        ]));
        $this->actingAs($adminUser)->patch(route('admin.request.approve', [
            'attendance_correct_request_id' => $attendanceRequest2->id,
        ]));

        $response = $this->actingAs($user)->get(route('attendance.request.list', [
            'tab' => 'approved',
        ]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('残業のため');
        $response->assertSee('遅刻のため');
        $response->assertSee($attendance->date->format('Y/m/d'));
        $response->assertSee($attendance2->date->format('Y/m/d'));
    }

    //勤怠修正--詳細
    /** @test */
    public function test_request_detail_link_navigates_to_attendance_detail(): void
    {
        [$user, $attendance] = $this->createDate();

        $response = $this->actingAs($user)->post(route('attendance.request', [
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d'),
        ]), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '19:00',
            'requested_break_start' => [''],
            'requested_break_end' => [''],
            'note' => '残業のため',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.request.list'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('残業のため');
        $response->assertSee($attendance->date->format('Y年'));
        $response->assertSee($attendance->date->format('n月j日'));
        $response->assertSee('承認待ちのため修正はできません。');
    }
}

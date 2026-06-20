<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class AdminRequestApprovalTest extends TestCase
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

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $date = Carbon::now();

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => $date->copy(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        $request1 = AttendanceRequest::create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'requested_clock_in' => '11:00:00',
            'requested_clock_out' => '18:00:00',
            'note' => '遅刻のため',
            'status' => 1,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user1->id,
            'date' => $date->copy()->addDay(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        $request2 = AttendanceRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user1->id,
            'requested_clock_in' => '09:00:00',
            'requested_clock_out' => '19:00:00',
            'note' => '残業のため',
            'status' => 2,
        ]);

        $attendance3 = Attendance::create([
            'user_id' => $user2->id,
            'date' => $date->copy(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        $request3 = AttendanceRequest::create([
            'attendance_id' => $attendance3->id,
            'user_id' => $user2->id,
            'requested_clock_in' => '09:00:00',
            'requested_clock_out' => '16:00:00',
            'note' => '早退のため',
            'status' => 2,
        ]);

        $attendance4 = Attendance::create([
            'user_id' => $user2->id,
            'date' => $date->copy()->addDay(),
            'clock_in' => '08:00:00',
            'clock_out' => '21:00:00',
        ]);
        $request4 = AttendanceRequest::create([
            'attendance_id' => $attendance4->id,
            'user_id' => $user2->id,
            'requested_clock_in' => '10:00:00',
            'requested_clock_out' => '18:00:00',
            'note' => '長時間労働のため',
            'status' => 1,
        ]);

        return compact('adminUser', 'user1', 'user2', 'date', 'attendance1', 'request1', 'attendance2', 'request2', 'attendance3', 'request3', 'attendance4', 'request4',);
    }

    //勤怠修正--承認待ち
    /** @test */
    public function test_admin_pending_requests_displays_all_pending_requests(): void
    {
        $data = $this->createDate();
        $adminUser = $data['adminUser'];
        $request1 = $data['request1'];
        $request4 = $data['request4'];

        $response = $this->actingAs($adminUser)->get(route('attendance.request.list'));

        $response->assertStatus(200);
        $response->assertViewHas('attendanceRequests', function ($attendanceRequests) use ($request1, $request4) {
            return $attendanceRequests->contains(fn($request) => $request->id === $request1->id)
                && $attendanceRequests->contains(fn($request) => $request->id === $request4->id)
                && $attendanceRequests->count() === 2;
        });
    }

    //勤怠修正--承認済み
    /** @test */
    public function test_approved_requests_displays_all_approved_requests(): void
    {
        $data = $this->createDate();
        $adminUser = $data['adminUser'];
        $request2 = $data['request2'];
        $request3 = $data['request3'];

        $response = $this->actingAs($adminUser)->get(route('attendance.request.list', [
            'tab' => 'approved',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('attendanceRequests', function ($attendanceRequests) use ($request2, $request3) {
            return $attendanceRequests->contains(fn($request) => $request->id === $request2->id)
                && $attendanceRequests->contains(fn($request) => $request->id === $request3->id)
                && $attendanceRequests->count() === 2;
        });
    }

    //勤怠修正--詳細表示
    /** @test */
    public function test_admin_request_detail_displays_correct_data(): void
    {
        $adminUser = User::factory()->create([
            'admin_status' => true,
        ]);
        $user = User::factory()->create();
        $date = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date->copy(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => '11:00:00',
            'requested_clock_out' => '18:00:00',
            'note' => '遅刻のため',
            'status' => 1,
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.request.approve.show', $attendanceRequest->id));

        $response->assertStatus(200);
        $response->assertViewHas('attendanceRequest', function ($viewAttendanceRequest) use ($attendanceRequest) {
            return $viewAttendanceRequest->id === $attendanceRequest->id;
        });
        $response->assertSee('遅刻のため');
    }

    //勤怠修正--修正
    /** @test */
    public function test_admin_request_approval_updates_attendance_correctly(): void
    {
        $adminUser = User::factory()->create([
            'admin_status' => true,
        ]);
        $user = User::factory()->create();
        $date = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date->copy()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => '11:00:00',
            'requested_clock_out' => '18:00:00',
            'note' => '遅刻のため',
            'status' => 1,
        ]);

        $response = $this->actingAs($adminUser)->patch(route('admin.request.approve', $attendanceRequest->id));

        $response->assertRedirect(route('admin.request.approve.show', $attendanceRequest->id));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '11:00:00',
            'clock_out' => '18:00:00',
        ]);
        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => '11:00:00',
            'requested_clock_out' => '18:00:00',
            'note' => '遅刻のため',
            'status' => 2,
        ]);
    }
}

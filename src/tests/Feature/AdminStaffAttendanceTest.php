<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class AdminStaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //ユーザー情報--取得
    /** @test */
    public function test_admin_staff_list_displays_all_users_name_and_email(): void
    {
        $adminUser = User::factory()->create([
            'admin_status' => true,
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($adminUser)->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee($user1->email);
        $response->assertSee($user2->email);
    }

    //ユーザー情報--勤怠
    /** @test */
    public function test_admin_staff_attendance_list_displays_correct_data(): void
    {
        $adminUser = User::factory()->create([
            'admin_status' => true,
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $date = Carbon::now()->format('Y-m-d');

        Attendance::create([
            'user_id' => $user1->id,
            'date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        Attendance::create([
            'user_id' => $user2->id,
            'date' => $date,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.attendance.staff', $user1->id));

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('8:00');
    }

    //ユーザー情報--前月ボタン
    /** @test */
    public function test_admin_staff_attendance_list_displays_previous_month_when_prev_clicked(): void
    {
        Carbon::setTestNow('2030-01-10 09:00:00');

        $adminUser = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        $baseDate = Carbon::now();
        $previousDate = $baseDate->copy()->subMonthNoOverflow()->format('Y-m-d');
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

        $response = $this->actingAs($adminUser)->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => -1,
        ]));

        $response->assertStatus(200);
        $response->assertSee($baseDate->copy()->subMonthNoOverflow()->format('Y/m'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');

        Carbon::setTestNow();
    }

    //ユーザー情報--翌月ボタン
    /** @test */
    public function test_admin_staff_attendance_list_displays_next_month_when_next_clicked(): void
    {
        Carbon::setTestNow('2030-01-10 09:00:00');

        $adminUser = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        $baseDate = Carbon::now();
        $nextDate = $baseDate->copy()->addMonthNoOverflow()->format('Y-m-d');
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

        $response = $this->actingAs($adminUser)->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => +1,
        ]));

        $response->assertStatus(200);
        $response->assertSee($baseDate->copy()->addMonthNoOverflow()->format('Y/m'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');

        Carbon::setTestNow();
    }

    //ユーザー情報--詳細ボタン
    /** @test */
    public function test_admin_staff_attendance_list_detail_link_navigates_to_detail(): void
    {
        $adminUser = User::factory()->create([
            'admin_status' => true,
        ]);

        $date = Carbon::now()->format('Y-m-d');

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
}

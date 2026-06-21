<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //公開API読み取り--一覧
    /** @test */
    public function test_index_returns_attendance_list_as_json(): void
    {
        $response = $this->getJson('/api/v1/attendance-records');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'user_name',
                    'date',
                    'clock_in',
                    'clock_out',
                    'total_time',
                    'total_break_time',
                    'comment',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }

    //公開API読み取り--詳細
    /** @test */
    public function test_show_returns_attendance_detail_as_json(): void
    {
        $attendance = Attendance::first();

        $response = $this->getJson('/api/v1/attendance-records/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user' => [
                    'id',
                    'name',
                ],
                'date',
                'clock_in',
                'clock_out',
                'breaks' => [
                    '*' => [
                        'id',
                        'break_in',
                        'break_out',
                    ],
                ],
                'applications',
                'comment',
            ]
        ]);
    }

    //公開API読み取り--詳細404エラー
    /** @test */
    public function test_show_returns_404_when_id_not_found(): void
    {
        $response = $this->getJson('/api/v1/attendance-records/' . 9999);

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }

    //公開API書き込み--勤怠作成
    /** @test */
    public function test_store_creates_new_attendance(): void
    {
        $user = User::factory()->create();

        $attendanceData = [
            'date' => '2030-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => null
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/attendance-records/', $attendanceData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
    }

    //公開API書き込み--勤怠作成422エラー
    /** @test */
    public function test_store_returns_422_with_japanese_error_on_validation_failure(): void
    {
        $user = User::factory()->create();

        $attendanceData = [
            'date' => '',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => null
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/attendance-records/', $attendanceData);

        $response->assertStatus(422);
        $response->assertJson([
            'errors' =>
            array(
                'date' =>
                array(
                    0 => '勤怠日は必須です。',
                ),
            ),
        ]);
    }

    //公開API書き込み--勤怠更新
    /** @test */
    public function test_update_modifies_existing_attendance(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2030-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => null
        ]);

        $attendanceData = [
            'date' => '2030-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '19:00:00',
            'comment' => null
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/attendance-records/' . $attendance->id, $attendanceData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '19:00:00',
        ]);
        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/attendance-records/' . 9999, $attendanceData);

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }

    //公開API書き込み--勤怠削除
    /** @test */
    public function test_destroy_deletes_attendance(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2030-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => null
        ]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/attendance-records/' . $attendance->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/attendance-records/' . 9999);

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }
}

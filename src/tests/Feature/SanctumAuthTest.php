<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //Sanctum認証--未認証401
    /** @test */
    public function test_write_apis_return_401_when_unauthenticated(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2030-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->postJson('/api/v1/attendance-records', [
            'date' => '2030-01-11',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        $response = $this->putJson('/api/v1/attendance-records/' . $attendance->id, [
            'date' => '2030-01-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        $response = $this->deleteJson('/api/v1/attendance-records/' . $attendance->id);
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    //Sanctum認証--更新、削除
    /** @test */
    public function test_authenticated_user_can_update_and_delete_own_attendance(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2030-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user);
        $response = $this->putJson('/api/v1/attendance-records/' . $attendance->id, [
            'date' => '2030-01-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);
        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
            'clock_out' => '18:00:00',
        ]);

        $response = $this->deleteJson('/api/v1/attendance-records/' . $attendance->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }

    //Sanctum認証--403
    /** @test */
    public function test_user_cannot_update_and_delete_others_attendance(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $otherUser->id,
            'date' => '2030-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user);
        $response = $this->putJson('/api/v1/attendance-records/' . $attendance->id, [
            'date' => '2030-01-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);

        $response = $this->deleteJson('/api/v1/attendance-records/' . $attendance->id);
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);
    }
}

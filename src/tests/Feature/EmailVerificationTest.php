<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //メール認証--メール送信
    /** @test */
    public function test_verification_email_is_sent_after_registration(): void
    {
        Notification::fake();

        $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    //メール認証--認証はこちらからボタン
    /** @test */
    public function test_resend_verification_link_navigates_to_verification_site(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)->get(route('verification.notice'))
            ->assertStatus(200)
            ->assertSee('認証はこちらから')
            ->assertSee('href="https://mailtrap.io"', false);
    }

    //メール認証--勤怠登録画面に遷移
    /** @test */
    public function test_email_verification_completion_redirects_to_attendance_index(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect(route('attendance.index'));
    }
}

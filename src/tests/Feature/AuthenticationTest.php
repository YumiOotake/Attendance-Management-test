<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //会員情報登録--名前バリデーション
    /** @test */
    public function test_register_user_validate_name(): void
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    //会員情報登録--メアドバリデーション
    /** @test */
    public function test_register_user_validate_email(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    //会員情報登録--パスワード8文字未満
    /** @test */
    public function test_register_user_validate_password_under8(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    //会員情報登録--パスワード不一致
    /** @test */
    public function test_register_user_validate_confirm_password(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['password_confirmation' => 'パスワードと一致しません']);
    }

    //会員情報登録--パスワードバリデーション
    /** @test */
    public function test_register_user_validate_password(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    //会員情報登録
    /** @test */
    public function test_register_user(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    //ログイン--メアドバリデーション
    /** @test */
    public function test_login_user_validate_email(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => '',
            'password' => 'password',
            'login_type' => 'user',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    //ログイン--パスワードバリデーション
    /** @test */
    public function test_login_user_validate_password(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'user1@example.com',
            'password' => '',
            'login_type' => 'user',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    //ログイン--不一致
    /** @test */
    public function test_login_user_with_invalid_credentials(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'different@example.com',
            'password' => 'password',
            'login_type' => 'user',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    //管理者ログイン--メアドバリデーション
    /** @test */
    public function test_login_admin_user_validate_email(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => '',
            'password' => 'password',
            'login_type' => 'admin',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    //管理者ログイン--パスワードバリデーション
    /** @test */
    public function test_login_admin_user_validate_password(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'user3@example.com',
            'password' => '',
            'login_type' => 'admin',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    //ログイン--不一致
    /** @test */
    public function test_login_admin_with_invalid_credentials(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'different@example.com',
            'password' => 'password',
            'login_type' => 'admin',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}


<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // SanctumのSPA認証（Cookieベース）はフロントのOrigin/Refererを見て
        // セッション系ミドルウェアを有効化するため、テストでも同様に付与する
        $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ]);
    }

    public function test_guest_cannot_access_protected_user_endpoint(): void
    {
        $this->getJson('/api/user')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_user_can_register_and_is_authenticated(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)->assertJsonPath('email', 'test@example.com');

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

        // ガードキャッシュを破棄し、次のリクエストでDBから再取得させる（test_user_can_logout参照）
        auth()->forgetGuards();

        $this->getJson('/api/user')->assertStatus(200)->assertJsonPath('email', 'test@example.com');
    }

    public function test_register_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertStatus(200)->assertJsonPath('email', $user->email);

        $this->getJson('/api/user')->assertStatus(200)->assertJsonPath('id', $user->id);
    }

    public function test_login_fails_with_incorrect_password(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertStatus(200);

        $this->postJson('/api/logout')->assertStatus(204);

        // auth:sanctumが使う'sanctum'ガード（RequestGuard）は解決結果を自身でキャッシュするため、
        // 同一テスト内で複数リクエストをシミュレートする場合はログアウト後に明示的に破棄する
        // （本番では各リクエストでコンテナが作り直されるため発生しない、テスト特有の事象）
        auth()->forgetGuards();

        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => $user->email])
            ->assertStatus(200);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create(['password' => 'old-password']);

        $this->postJson('/api/forgot-password', ['email' => $user->email])
            ->assertStatus(200);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->postJson('/api/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ])->assertStatus(200);

            return true;
        });

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'new-password123',
        ])->assertStatus(200);
    }
}

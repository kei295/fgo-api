<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // APIのみの構成で `password.reset` という名前付きルートが存在しないため、
        // パスワードリセットメールのリンク先をNext.jsフロントエンドのページに向ける
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            $email = $notifiable->getEmailForPasswordReset();

            return rtrim(config('app.frontend_url'), '/')."/reset-password?token={$token}&email={$email}";
        });
    }
}

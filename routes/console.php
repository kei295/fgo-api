<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Atlas Academy APIから新サーヴァント追加を検知し、週1回 servants テーブルへ自動同期する
Schedule::command('servants:sync')->weekly();

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Atlas Academy API
    |--------------------------------------------------------------------------
    |
    | FGOのゲームデータを取得する外部APIの接続設定。
    | https://api.atlasacademy.io
    |
    */

    'base_url' => env('ATLAS_ACADEMY_BASE_URL', 'https://api.atlasacademy.io'),

    'region' => env('ATLAS_ACADEMY_REGION', 'JP'),

    'lang' => env('ATLAS_ACADEMY_LANG', 'en'),

    // detail取得時の同時リクエスト数
    'concurrency' => (int) env('ATLAS_ACADEMY_CONCURRENCY', 10),
];

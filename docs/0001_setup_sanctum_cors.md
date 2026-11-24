# 0001: Laravelプロジェクト作成・Sanctum / CORS設定

## 概要
Laravel 11 APIの基盤設定。SPA認証（Sanctum, Cookieベース）とフロント（Next.js/Vercel）からの
CORSアクセスを有効化する。詳細は `CLAUDE.md` の「認証設計」「デプロイ構成」を参照。

## TODO
【☑️】Laravelプロジェクトの初期状態を確認（既存リポジトリのため新規作成は不要。実体は Laravel Framework 13.34.0 — `CLAUDE.md` 側の表記を修正済み）
【☑️】`laravel/sanctum` インストール・設定ファイル発行（`php artisan install:api` で実施。`routes/api.php` 作成、`personal_access_tokens` テーブルmigration実行済み）
【☑️】`config/sanctum.php` の `stateful` ドメイン設定（`SANCTUM_STATEFUL_DOMAINS` 環境変数で制御）
【☑️】`.env` / `.env.example` に `FRONTEND_URLS` / `SANCTUM_STATEFUL_DOMAINS` を追加（`SESSION_DOMAIN` はローカルは `null` のままでOK）
【☑️】`config/cors.php` を公開し、`FRONTEND_URLS` 環境変数からオリジンを許可する設定に変更（`supports_credentials` を `true` に）
【☑️】`bootstrap/app.php` の `withMiddleware` に `$middleware->statefulApi();` を追加（`EnsureFrontendRequestsAreStateful` 有効化）
【☑️】ローカル環境で `GET /sanctum/csrf-cookie` のCORS/Cookie発行を確認（`Access-Control-Allow-Origin` / `XSRF-TOKEN` 発行をcurlで確認済み）
【☑️】未認証時に `auth:sanctum` 保護エンドポイントへアクセスすると `Route [login] not defined` で500になる不具合を修正（`bootstrap/app.php` に `$middleware->redirectGuestsTo(fn () => null);` を追加し、常にJSON 401を返すように変更）
【 】本番（Railway/Vercel）ドメインでの動作確認は 0010 デプロイ時に実施
【☑️】ログインAPI（0004）実装後、CSRF取得→ログインの一連の流れを再確認（0004でcurlによるE2E確認済み）

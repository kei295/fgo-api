# 0004: 認証API実装（register / login / logout）

## 概要
`CLAUDE.md` の「API設計 > Laravel（自前API）」「認証設計」節に対応。
メールアドレス＋パスワードによるSanctum SPA認証を実装する。

## TODO
【☑️】`POST /api/register`（新規登録）実装（`app/Http/Controllers/Api/AuthController.php`、登録と同時にSanctum認証Cookie発行）
【☑️】`POST /api/login`（ログイン、Sanctum認証Cookie発行）実装
【☑️】`POST /api/logout`（ログアウト、認証必要）実装
【☑️】`GET /api/user`（ログインユーザー情報取得、認証必要）実装（0001で作成済みのルートを流用）
【☑️】パスワードリセット機能の実装
  - `POST /api/forgot-password`（メール送信、`app/Http/Controllers/Api/PasswordResetController.php`）
  - `POST /api/reset-password`（token/email/passwordを受け取り新パスワードを設定）
  - メール内リンクの飛び先をNext.jsフロントエンド（`FRONTEND_URL` + `/reset-password`）に変更（`app/Providers/AppServiceProvider.php`の`ResetPassword::createUrlUsing`）
  - `CLAUDE.md`の画面構成に`/reset-password`（新パスワード設定画面）を追記（元の仕様には無かったが、メールリンクの受け先として必須のため追加）
【☑️】`auth:sanctum` ミドルウェアによる保護エンドポイントの設定（`/api/user`, `/api/logout`）
【☑️】バリデーション実装（email unique、`Password::defaults()`によるパスワード強度チェック、confirmed）
【☑️】curlによるエンドツーエンド動作確認（register→Cookie認証→logout→login→forgot-password→メール内リンクのtoken→reset-password→新パスワードでlogin、すべて成功。テストユーザーは削除済み）
【☑️】Featureテスト作成（`tests/Feature/AuthTest.php`、register / email重複 / login成功・失敗 / logout / forgot-password / reset-password、全8テスト・28アサーション、`php artisan test`で全件pass）
  - 補足: Sanctumの`sanctum`ガードはテストの1メソッド内で複数リクエストをシミュレートする際に解決結果を自身でキャッシュするため、ログアウト直後や新規登録直後に`auth()->forgetGuards();`で明示的に破棄している（本番では各リクエストでコンテナが作り直されるため発生しないテスト特有の事象）

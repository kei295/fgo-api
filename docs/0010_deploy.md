# 0010: Vercel + Railway へデプロイ

## 概要
`CLAUDE.md` の「デプロイ構成」節に対応。フロント（Vercel）・バックエンド/DB（Railway）への
本番デプロイとドメイン・CORS設定を行う。

## TODO
【 】Railwayプロジェクト作成（Laravel + MySQL）
【 】Laravel本番環境変数設定（`.env`, `APP_KEY`, DB接続情報など）
【 】Railway上での `php artisan migrate` 実行
【 】Railway上でのServant Seeder初回実行
【 】Railwayスケジューラ（`schedule:run`）の定期実行設定
【 】Vercelプロジェクト作成・GitHub連携（自動デプロイ）
【 】Next.js本番環境変数設定（Laravel APIのURLなど）
【 】本番ドメインに合わせた `config/cors.php` / `SANCTUM_STATEFUL_DOMAINS` の設定
【 】本番環境での一連の動作確認（登録→ログイン→所持登録→削除→ログアウト）

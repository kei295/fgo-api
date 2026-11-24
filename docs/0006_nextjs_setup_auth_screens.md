# 0006: Next.jsプロジェクト作成・認証画面実装

## 概要
`CLAUDE.md` の「画面構成」節のうち、認証関連画面を実装する。
Next.js (App Router, TypeScript) プロジェクトのセットアップを含む。
リポジトリは `/Users/fujita/fgo-web`（`fgo-api` とは別リポジトリ）。

## TODO
【☑️】Next.js (App Router) + TypeScript プロジェクト作成（`create-next-app`、Tailwind / ESLint / Turbopack込み。Next.js 16.3.8 / React 19.2.8）
【☑️】Laravel Sanctum Cookie認証と連携するAPIクライアント実装（`src/lib/api.ts`）
  - CSRF Cookie取得（`GET /sanctum/csrf-cookie`）→ `X-XSRF-TOKEN` ヘッダー付与 → `credentials: "include"` でfetch
  - 重要な設計判断: SanctumのセッションCookieはLaravel側のドメインに対して発行されるため、Next.jsのRoute Handlers/Server Actionsを経由せず、**ブラウザから直接fgo-apiへfetchする**構成にした（`src/lib/api.ts`冒頭にコメントで明記）
【☑️】`/`（ランディングページ）実装（サービス紹介・ログイン/新規登録へのリンク。ログイン中は名前表示＋ログアウトボタン）
【☑️】`/register`（新規登録フォーム）実装
【☑️】`/login`（ログインフォーム）実装
【☑️】`/forgot-password`（パスワードリセット メール送信フォーム）実装
【☑️】`/reset-password`（新パスワード設定フォーム、`token`/`email`をクエリパラメータで受け取る）実装
  - 元のチケットTODOには無かったが、`CLAUDE.md`の画面構成・0004での実装に合わせて追加
【☑️】未ログイン時のルートガード（保護ページへのアクセス制御）実装（`src/hooks/use-require-auth.ts`）
  - SanctumのCookieはNext.jsサーバー（proxy等）からは読めないため、クライアント側でのチェック＋リダイレクトのみで実装
  - 動作確認用に `/servants` に仮ページを作成し、ガードを適用（本実装はチケット0007）
【☑️】ログイン状態のグローバル管理（`src/contexts/auth-context.tsx`、`AuthProvider`/`useAuth`。`src/app/layout.tsx`でラップ）
【☑️】`npm run lint` / `npx tsc --noEmit` / `npm run build` すべて成功を確認
【☑️】Playwright + システムのGoogle Chromeで実ブラウザのE2E確認（スクリプトは確認後に削除）
  - register → `/servants` へリダイレクト・ログイン状態表示
  - logout → `/login` へリダイレクト
  - 未ログインで `/servants` に直接アクセス → `/login` にガードされる
  - login → `/servants` へリダイレクト、トップページでもログイン状態反映
  - forgot-password → Laravelのログ（`MAIL_MAILER=log`）からリンクを取得 → reset-password → 新パスワードでログイン成功
  - テストで作成したユーザーはすべて削除済み

## 既知の注意点
- `npm audit` で `braces`（eslint-config-nextの推移的依存、devのみ）に関する高深刻度の警告が出るが、
  修正には `eslint-config-next` を14系へダウングレードする必要があり逆行のため見送り。本番ビルド・実行には影響しない。
- ローカル開発時、ポート3000が他プロジェクトで使用中の場合はNext.jsが自動的に3001等へフォールバックする。
  その場合は `fgo-api/.env` の `FRONTEND_URLS` / `SANCTUM_STATEFUL_DOMAINS` に該当ポートを追加する必要がある
  （今回 `localhost:3001` を追加済み）。

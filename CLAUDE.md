# FGO 手持ちサーヴァント管理サービス

Fate/Grand Order（FGO）の手持ちサーヴァントを登録・管理できるポートフォリオ用Webサービス。
Laravel（API）+ Next.js（フロント）構成。本ファイルはこのリポジトリ（Laravel API側）の仕様メモ。

## 技術スタック

| レイヤー | 技術 | 備考 |
|---|---|---|
| フロントエンド | Next.js (App Router) | TypeScript使用 |
| バックエンド | Laravel 13 | REST API（リポジトリの実体は Laravel Framework 13.34.0） |
| データベース | MySQL | Railwayで運用 |
| 認証 | Laravel Sanctum | SPA認証（Cookieベース） |
| 外部API | Atlas Academy API | FGOゲームデータ（JP） |
| フロントデプロイ | Vercel | GitHubと自動連携 |
| バックエンドデプロイ | Railway | Laravel + MySQL |
| バージョン管理 | GitHub | |

将来追加予定: Google OAuth / GitHub OAuth（Laravel Socialite）

## MVP機能要件

### 認証
- [ ] メールアドレス＋パスワードによる新規登録
- [ ] ログイン / ログアウト
- [ ] パスワードリセット（メール送信）

### サーヴァント一覧（`/servants`）
- [ ] servantsテーブルから全サーヴァントを一覧表示
- [ ] クラス・レアリティ・名前で絞り込み
- [ ] 所持済みサーヴァントに「所持済み」チェックを表示
- [ ] 未所持サーヴァントに「登録」ボタンを表示し、押すと所持登録
- [ ] 所持済みサーヴァントに「登録解除」ボタンを表示し、押すと手持ちから削除
- [ ] サーヴァント画像クリックで詳細ページへ遷移

### 所持サーヴァント一覧（`/my-servants`）
- [ ] 登録済みサーヴァントのみ表示
- [ ] 対応する絆礼装をカード形式で表示
- [ ] 「登録解除」ボタンで手持ちから削除
- [ ] サーヴァント画像クリックで詳細ページへ遷移

### サーヴァント詳細（`/servants/[id]`）
- [ ] 霊基再臨段階に応じたイラスト表示
- [ ] 宝具名・クラス・属性・レアリティ・絆礼装の表示
- [ ] 育成状況（レベル・スキル・絆レベルなど）の表示
- [ ] 育成状況の編集（所持サーヴァントのみ。霊基再臨は表示・編集とも対象外）※Phase 3から前倒し

### servantsマスターデータ管理
- [ ] Atlas Academy APIからデータを取得するSeederの作成（初回投入）
- [ ] Laravelスケジューラによる自動差分同期（詳細は「マスターデータ同期方式」参照）

### スコープ外（MVP後 / Phase 2・3）
- Google / GitHub OAuthログイン
- 絆ポイント進捗管理

## 画面構成

| 画面名 | パス | 説明 |
|---|---|---|
| ランディングページ | `/` | サービス紹介・ログイン/新規登録へのリンク |
| 新規登録 | `/register` | メール・パスワード入力フォーム |
| ログイン | `/login` | メール・パスワード入力フォーム |
| パスワードリセット（メール送信） | `/forgot-password` | メール送信フォーム |
| パスワードリセット（新パスワード設定） | `/reset-password` | メール内リンクからアクセス。`token` / `email` をクエリパラメータで受け取り新パスワードを設定 |
| サーヴァント一覧 | `/servants` | 要ログイン。クラス・レアリティ・名前で絞り込み、所持チェック |
| 所持サーヴァント一覧 | `/my-servants` | 要ログイン。手持ちのみ表示、絆礼装カード表示 |
| サーヴァント詳細 | `/servants/[id]` | 要ログイン。再臨イラスト・宝具・育成状況表示 |

### メイン画面のUXフロー
1. クラスタブでサーヴァントを絞り込む
2. サーヴァントカードをクリックして手持ちに登録（緑のハイライト）
3. 画面下部に「手持ちに対応する絆礼装」が自動更新される
4. 手持ちのみ表示フィルターで登録済みキャラを確認できる

## データベース設計

3テーブル構成。`servants` はAtlas Academy APIから取得したマスターデータのキャッシュ、`user_servants` はユーザー固有の所持・育成状況を管理する。

### users（Laravelデフォルト）

| カラム | 型 | 備考 |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| email | varchar | unique |
| password | varchar | bcryptハッシュ |
| email_verified_at | timestamp | null許容 |
| remember_token | varchar | |
| created_at / updated_at | timestamp | |

### servants（Atlas Academy APIのマスターデータキャッシュ）

| カラム | 型 | 備考 |
|---|---|---|
| id | bigint PK | Atlas AcademyのサーヴァントIDをそのまま使用 |
| collection_no | int | 図鑑番号 |
| name | varchar | サーヴァント名（英語） |
| class_name | varchar | Saber / Archer など |
| rarity | tinyint unsigned | 1〜5 |
| attribute | varchar | 天・地・人・星・獣 |
| np_name | varchar | 宝具名 |
| np_type | varchar | 宝具種別（Arts / Buster / Quick） |
| bond_ce_id | int | 絆礼装のID（null許容） |
| bond_ce_name | varchar | 絆礼装名（null許容） |
| face_url | varchar | 顔画像URL（第1再臨） |
| ascension_image_urls | json | 再臨段階0〜4の画像URLをJSON配列で保持 |
| bond_ce_image_url | varchar | 絆礼装画像URL（null許容） |
| created_at / updated_at | timestamp | |

### user_servants（ユーザー固有の所持・育成状況）

| カラム | 型 | デフォルト | 備考 |
|---|---|---|---|
| id | bigint PK | — | |
| user_id | bigint FK | — | `users.id` |
| servant_id | bigint FK | — | `servants.id` |
| level | tinyint unsigned | 1 | 1〜120。MVPでは編集UIなし、デフォルト値のまま |
| skill_1 | tinyint unsigned | 1 | スキル1レベル（1〜10） |
| skill_2 | tinyint unsigned | 1 | スキル2レベル（1〜10） |
| skill_3 | tinyint unsigned | 1 | スキル3レベル（1〜10） |
| bond_level | tinyint unsigned | 0 | 絆レベル（0〜16） |
| np_level | tinyint unsigned | 1 | 宝具レベル（1〜5） |
| ascension | tinyint unsigned | 0 | 霊基再臨段階（0〜4） |
| created_at / updated_at | timestamp | — | |

INDEX: `user_id` + `servant_id` にユニーク制約（同一ユーザーが同じキャラを重複登録しない）

育成状況カラム（level / skill_1-3 / bond_level / np_level / ascension）は登録時にデフォルト値が入り、
所持サーヴァント詳細ページから `PATCH /api/servants/{servant_id}` で編集できる（Phase 3から前倒し）。
ただし `ascension`（霊基再臨）は画面での表示・編集の対象外とし、カラムのみ残している。

## マスターデータ同期方式

`servants` テーブルはAtlas Academy APIのキャッシュであり、以下の2系統で同期する。

1. **初回投入（Seeder）**: `php artisan db:seed --class=ServantSeeder` などで
   `GET /export/JP/basic_servant_lang_en.json` ＋ 詳細 `GET /nice/JP/servant/{id}?lang=en` を取得し、
   `servants` テーブルを一括投入する。
2. **自動差分同期（Laravelスケジューラ）**: 週1回程度の頻度でスケジュールタスクを実行し、
   Atlas Academy APIから最新のサーヴァント一覧を取得して `servants` テーブルに存在しないIDがあれば
   新規レコードとしてinsertする（新サーヴァント追加への追従）。既存レコードの更新は対象外で、
   新規追加の検知のみを行う。
   - 実装イメージ: 専用の `artisan` コマンド（例: `servants:sync`）を作成し、
     `routes/console.php` または `app/Console/Kernel.php` の `schedule()` で `->weekly()` 登録する。

## API設計

### Laravel（自前API）

| メソッド | エンドポイント | 説明 | 認証 |
|---|---|---|---|
| POST | `/api/register` | 新規登録 | 不要 |
| POST | `/api/login` | ログイン | 不要 |
| POST | `/api/forgot-password` | パスワードリセットメール送信 | 不要 |
| POST | `/api/reset-password` | パスワードリセット実行（token / email / password） | 不要 |
| POST | `/api/logout` | ログアウト | 必要 |
| GET | `/api/user` | ログインユーザー情報取得 | 必要 |
| GET | `/api/servants` | 手持ちサーヴァント一覧取得 | 必要 |
| POST | `/api/servants` | 手持ちに追加 | 必要 |
| PATCH | `/api/servants/{servant_id}` | 育成状況の更新（level / skill_1-3 / np_level / bond_level。ascensionは対象外） | 必要 |
| DELETE | `/api/servants/{servant_id}` | 手持ちから削除 | 必要 |

### Atlas Academy API（外部・読み取り専用）

| 用途 | エンドポイント |
|---|---|
| 全サーヴァント一覧 | `GET /export/JP/basic_servant_lang_en.json` |
| サーヴァント詳細（絆礼装含む） | `GET /nice/JP/servant/{id}?lang=en` |

Atlas Academy APIへの**画面表示用**リクエストはNext.js側（Route Handlers）から行い、Laravelには渡さない。
Laravel側がAtlas Academy APIを叩くのは「マスターデータ同期方式」のSeeder／スケジュールタスクのみ。

## 認証設計

Laravel Sanctum の SPA認証（Cookieベース）を採用。JWTトークンではなくCookieを使うことで、
Next.jsとLaravelを同一ドメイン（またはサブドメイン）に配置した場合にセキュアな認証が実現できる。

### 認証フロー
1. Next.jsから `/api/login` にPOST
2. Sanctumが認証Cookieを発行
3. 以降のAPIリクエストにCookieが自動付与される
4. 保護されたエンドポイントは `auth:sanctum` ミドルウェアで保護

### MVP認証方式
- メールアドレス＋パスワード（Laravel Sanctum）

### 将来追加
- Google OAuth（Laravel Socialite）
- GitHub OAuth（Laravel Socialite）

## デプロイ構成

| 役割 | サービス | 備考 |
|---|---|---|
| フロントエンド | Vercel | Next.jsのホスティング。GitHubプッシュで自動デプロイ |
| バックエンド | Railway | LaravelアプリのPHPサーバー |
| データベース | Railway（MySQL） | Laravelと同一プロジェクト内で管理 |

### ドメイン・CORS設定の注意点
Vercel（フロント）と Railway（Laravel API）はドメインが異なるため、CORSの設定が必要。
Laravel側で `config/cors.php` に許可オリジンを設定する。SanctumのCookieベース認証を使う場合は
フロントとバックでサブドメインを揃えることが理想だが、ポートフォリオ用途では
`stateful_domains` 設定で対応する。

## ロードマップ

| フェーズ | 内容 | 目標 |
|---|---|---|
| Phase 1 — MVP | 認証・手持ち登録・絆礼装可視化 | ポートフォリオとして公開できる状態 |
| Phase 2 — 認証拡張 | Google / GitHub OAuthログイン追加 | Socialite導入 |
| Phase 3 — 進捗管理 | 絆ポイントの進捗記録・可視化（育成状況の編集は前倒しで実装済み） | キャラごとの絆レベル・ポイント数を入力・グラフ表示 |

### Phase 1 の開発ステップ
1. Laravelプロジェクト作成・Sanctum / CORS設定
2. マイグレーション作成（`servants` / `users` / `user_servants`）
3. Atlas Academy APIからデータ取得するSeeder作成・実行、自動差分同期タスクの実装
4. 認証API実装（register / login / logout）
5. `user_servants` API実装（一覧取得・登録・削除）
6. Next.jsプロジェクト作成・認証画面実装
7. サーヴァント一覧画面実装（`/servants`）
8. 所持サーヴァント一覧画面実装（`/my-servants`）
9. サーヴァント詳細画面実装（`/servants/[id]`）
10. Vercel + Railway へデプロイ

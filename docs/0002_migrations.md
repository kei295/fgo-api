# 0002: マイグレーション作成（servants / users / user_servants）

## 概要
`CLAUDE.md` の「データベース設計」節のテーブル定義をマイグレーションとして実装する。
`users` はLaravelデフォルトのまま使用。

## TODO
【☑️】`users` テーブルがデフォルト定義のままであることを確認（変更不要）
【☑️】`servants` テーブルのマイグレーション作成（`database/migrations/2026_10_06_120132_create_servants_table.php`）
  - id（unsignedBigInteger PK, auto increment無効。Atlas AcademyのサーヴァントIDをそのまま使用）
  - collection_no（int, 図鑑番号）
  - name（varchar）
  - class_name（varchar）
  - rarity（tinyint unsigned, 1〜5）
  - attribute（varchar）
  - np_name（varchar）
  - np_type（varchar）
  - bond_ce_id（int, null許容）
  - bond_ce_name（varchar, null許容）
  - face_url（varchar）
  - ascension_image_urls（json）
  - bond_ce_image_url（varchar, null許容）
  - timestamps
【☑️】`user_servants` テーブルのマイグレーション作成（`database/migrations/2026_10_06_120133_create_user_servants_table.php`）
  - id（bigint PK）
  - user_id（bigint FK → users.id, cascadeOnDelete）
  - servant_id（bigint FK → servants.id, cascadeOnDelete）
  - level（tinyint unsigned, default 1）
  - skill_1 / skill_2 / skill_3（tinyint unsigned, default 1）
  - bond_level（tinyint unsigned, default 0）
  - np_level（tinyint unsigned, default 1）
  - ascension（tinyint unsigned, default 0）
  - timestamps
【☑️】`user_servants` に `user_id` + `servant_id` のユニーク制約を追加
【☑️】Eloquentモデル（`Servant`, `UserServant`）とリレーション定義（`User::userServants()` / `Servant::userServants()` / `UserServant::user()` / `UserServant::servant()`）。`User`に`Laravel\Sanctum\HasApiTokens`も追加
【☑️】`php artisan migrate` の動作確認（tinkerでservant/user/user_servant作成、デフォルト値・ユニーク制約違反を確認、テストデータは削除済み）

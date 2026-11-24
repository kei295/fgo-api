# 0003: Atlas Academy APIからのSeeder作成・自動差分同期

## 概要
`CLAUDE.md` の「マスターデータ同期方式」節に対応。`servants` テーブルの初回投入（Seeder）と、
Laravelスケジューラによる週次の自動差分同期（新サーヴァント追加の検知）を実装する。

## TODO
【☑️】Atlas Academy APIクライアント実装（`app/Services/AtlasAcademy/AtlasAcademyClient.php`）
  - `basicServantList()`: `GET /export/JP/basic_servant_lang_en.json` を取得し、プレイアブルなサーヴァントのみフィルタ
  - `servantDetails()` / `craftEssenceDetails()`: `GET /nice/JP/servant/{id}` ・ `GET /nice/JP/equip/{id}` を`Http::pool`で並列取得（同時実行数は`config/atlasacademy.php`の`concurrency`で調整可）
【☑️】JSON→DBカラムのマッピング実装（`app/Services/AtlasAcademy/ServantMapper.php`）
  - クラス名・属性（天/地/人/星/獣）・宝具名・宝具種別（Quick/Arts/Buster、最もpriorityが高いものを採用）・再臨段階ごとの顔画像URL・絆礼装名/画像URLを変換
  - ※宝具種別のカードコード対応に誤りがあり、0009で修正（1=Arts / 2=Buster / 3=Quick）
【☑️】`ServantSeeder` 作成（`database/seeders/ServantSeeder.php`、全413件のプレイアブルサーヴァントを取得し`servants`テーブルへupsert）
【☑️】`php artisan db:seed --class=ServantSeeder` の動作確認（413件を投入、bond_ce_id欠損0件・np_name欠損0件を確認）
【☑️】差分同期用の `artisan` コマンド作成（`app/Console/Commands/SyncServants.php`、`servants:sync`。`servants`テーブルに存在しないIDのみ検知してinsert）
【☑️】共通ロジックを`App\Services\AtlasAcademy\ServantSyncService`に切り出し、Seederとsyncコマンドの両方から利用
【☑️】`routes/console.php` に `Schedule::command('servants:sync')->weekly();` を登録（`php artisan schedule:list`で確認済み）
【☑️】新規・差分同期それぞれの動作確認（新規3件 → 残り410件を正しく検知、再実行時は「新規サーヴァントはありません」と表示されることを確認）
【☑️】新サーヴァント追加時の手動同期手順をREADMEに記載
【 】Railway本番環境での`schedule:run`定期実行cron設定は 0010 デプロイ時に実施

# 0005: user_servants API実装（一覧取得・登録・削除）

## 概要
`CLAUDE.md` の「API設計 > Laravel（自前API）」節に対応。ログインユーザーの手持ちサーヴァント管理API。
育成状況（level / skill / bond_levelなど）の更新APIはMVPスコープ外（Phase 3）。

## TODO
【☑️】`GET /api/servants`（手持ちサーヴァント一覧取得、認証必要）実装（`UserServantController@index`、`servant`リレーションをeager load）
【☑️】`POST /api/servants`（手持ちに追加、認証必要）実装
【☑️】`DELETE /api/servants/{servant_id}`（手持ちから削除、認証必要）実装
【☑️】`user_id` + `servant_id` の重複登録防止バリデーション（422、`servant_id`に日本語エラーメッセージ）
【☑️】存在しない `servant_id` を指定した場合のエラーハンドリング（`exists:servants,id`で422）／他ユーザーの所持・未所持サーヴァントを削除しようとした場合は404
【☑️】登録時に育成状況カラムがデフォルト値（level=1, skill=1, bond_level=0, np_level=1, ascension=0）で作成されることを確認
  - 実装時に見つけたバグ: `create()`直後のモデルはDBデフォルト値を保持しないため、レスポンスに`level`等が欠落していた。`$userServant->refresh()`を挟んで修正
【☑️】Featureテスト作成（`tests/Feature/UserServantTest.php`、未認証拒否・一覧の他ユーザー分離・登録・重複登録エラー・存在しないID・削除・他ユーザーの所持削除不可、全8テスト・26アサーション、`php artisan test`で全件pass）
【☑️】実機（curl）でもAtlas Academyから投入済みの実データ（Altria Pendragon, id=100100）を使い一覧・登録・重複エラー・削除・404を確認

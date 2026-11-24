# 0009: サーヴァント詳細画面実装（/servants/[id]）

## 概要
`CLAUDE.md` の「MVP機能要件 > サーヴァント詳細」「画面構成」節に対応。
育成状況は表示のみ（編集UIはPhase 3でスコープ外）。

## TODO
【☑️】`/servants/[id]` 画面の実装（`src/app/servants/[id]/page.tsx`）
  - 表示用データはNext.jsのRoute Handler `GET /api/atlas/servants/[id]`（`src/app/api/atlas/servants/[id]/route.ts`）経由でAtlas Academyから取得
    - `GET /nice/JP/servant/{id}?lang=en` ＋ 絆礼装 `GET /nice/JP/equip/{bondEquip}?lang=en` を取得し、画面に必要な項目だけに整形（1日キャッシュ）
    - 数値以外のID・存在しないID・プレイアブルでないID（敵専用など）は404 →「サーヴァントが見つかりません。」を表示
  - Route Handler共通の定数・`isPlayableServant`・`fetchAtlasJson` を `src/lib/atlas-server.ts` に切り出し、一覧のRoute Handlerもこれを使うよう整理
  - 所持状況は `GET /api/servants` から該当 `servant_id` を探して取得
【☑️】霊基再臨段階（`ascension_image_urls`）に応じたイラスト表示
  - DBの `ascension_image_urls` は顔アイコンのため、詳細画面ではAtlasの立ち絵（`extraAssets.charaGraph.ascension`、512x724）を使用
  - 第1段階〜最終再臨のタブで切り替え可能。初期表示は所持状況の `ascension`（0〜4）に対応する段階（0→第1、1〜2→第2、3→第3、4→最終。`ascensionStageOf`）
【☑️】宝具名・クラス・属性・レアリティの表示
  - 宝具はpriorityが最も高いもの（強化後の現在の宝具）を採用。日本語名・ルビ・英語名・カード種別（色付きバッジ）・ランク・種別を表示
【☑️】絆礼装の表示（礼装カード画像（CharaGraph、512x875）＋日本語名・英語名）
【☑️】育成状況（レベル・スキル・絆レベルなど）の表示（表示のみ、編集UIなし。「今後追加予定」と注記）
【☑️】未所持サーヴァント詳細を開いた場合の表示（育成状況なし）の挙動確認
  - 「未所持のサーヴァントです」と表示し、その場で「手持ちに登録」できるボタンを設置。登録後は育成状況（デフォルト値）に切り替わる
【☑️】`npm run lint` / `npx tsc --noEmit` / `npm run build` すべて成功を確認
【☑️】Playwright + システムのGoogle Chromeで実ブラウザのE2E確認（スクリプトは確認後に削除）
  - 未ログインガード、`/servants`・`/my-servants` からの遷移
  - メルトリリス（未所持）: アルターエゴ / 地 / ★5、宝具「弁財天五弦琵琶」Quick EX、絆礼装「輝くばかりの」、再臨タブ切替で立ち絵URLが変わる
  - 詳細画面から登録 → 育成状況 Lv1 / 再臨0 / 宝具Lv1 / 絆0 / スキル1/1/1、リロード後も保持
  - マシュ「希望築く人理の盾」Arts、アルトリア「約束された勝利の剣」Buster
  - `/servants/99999999`・`/servants/abc`・`/servants/9939910`（非プレイアブル）は「見つかりません」
  - 390px幅で横スクロールなし。テストで作成したユーザーはすべて削除済み

## 実装中に見つけて修正したバグ（fgo-api側）
- `app/Services/AtlasAcademy/ServantMapper.php` の宝具カードのコード対応が誤っていた（`1=Quick / 2=Arts / 3=Buster` としていたが、
  正しくは `1=Arts / 2=Buster / 3=Quick`）。そのため `servants.np_type` が全件ずれていた（例: アルトリアがArts、メルトリリスがBuster）
  - 対応表を修正し、`tests/Unit/ServantMapperTest.php` を追加（カード種別3パターン＋priority選択）。`php artisan test` 全22件pass
  - `php artisan db:seed --class=ServantSeeder` を再実行（upsertのため既存行も更新）し、DBの `np_type` を修正済み（Arts 159 / Buster 158 / Quick 96）
  - **本番（Railway）へ初回投入する際は修正後のコードでSeederを実行すること**（0010）

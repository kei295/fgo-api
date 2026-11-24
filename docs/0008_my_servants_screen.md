# 0008: 所持サーヴァント一覧画面実装（/my-servants）

## 概要
`CLAUDE.md` の「MVP機能要件 > 所持サーヴァント一覧」「画面構成」節に対応。

## TODO
【☑️】`/my-servants` 画面で登録済みサーヴァントのみ表示（`GET /api/servants`、`src/app/my-servants/page.tsx`）
  - 図鑑番号順。クラス（日本語表記）・レアリティ・英語名を表示
  - fgo-apiの`servants`テーブルは英語名のみのため、日本語名は `/api/atlas/servants`（0007のRoute Handler）から補完。取得失敗時は英語名で表示を継続
  - 未所持時は空状態メッセージと `/servants` への導線を表示
【☑️】対応する絆礼装をカード形式で表示（各サーヴァントカード内に絆礼装の画像＋名前）
【☑️】「登録解除」ボタン実装（`DELETE /api/servants/{servant_id}`）
  - 誤操作防止のため確認ダイアログを表示。404（別タブ等で解除済み）の場合も画面から取り除く
【☑️】サーヴァント画像クリックで `/servants/[id]` へ遷移（遷移先の画面はチケット0009で実装）
【☑️】手持ちのみ表示フィルターとの整合性確認（`/servants` 側フィルターとの連携）
  - `/servants` の「手持ちのみ表示」と `/my-servants` の表示内容・順序が一致することを確認
  - `/my-servants` で解除 → `/servants` で所持数が減り「登録」ボタンに戻ることを確認（各画面はマウント時に `GET /api/servants` を取り直す）
【☑️】`src/lib/atlas.ts` の `classTabOf` をfgo-api側の表記（`AlterEgo` など ucfirst 済み）にも対応させ、`classLabelOf`（クラス名の日本語表示）を追加
【☑️】`npm run lint` / `npx tsc --noEmit` / `npm run build` すべて成功を確認
【☑️】Playwright + システムのGoogle Chromeで実ブラウザのE2E確認（スクリプトは確認後に削除）
  - 未ログインガード、空状態、3騎（シールダー / セイバー / アルターエゴ）登録後の表示・絆礼装画像の読み込み
  - 登録解除（確認ダイアログ）→ リロード後も反映、`/servants` 側にも反映
  - 画像クリックで `/servants/800100` へ遷移、390px幅で横スクロールなし
  - テストで作成したユーザーはすべて削除済み

## 既知の注意点
- マシュ・キリエライト（id=800100）のみ、Atlas Academyの `basic_servant` エクスポートではレアリティ★3、
  `nice/servant` 詳細では★4とデータが食い違う。そのため `/servants`（Atlas一覧）では★3、
  `/my-servants`（fgo-apiのDB＝詳細由来）では★4と表示が異なる。他の412騎は一致を確認済み。

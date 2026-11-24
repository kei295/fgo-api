# 0007: サーヴァント一覧画面実装（/servants）

## 概要
`CLAUDE.md` の「MVP機能要件 > サーヴァント一覧」「画面構成」節に対応。
サーヴァントのマスターデータはNext.js側のRoute HandlersからAtlas Academy APIを取得して表示する。

## TODO
【☑️】Atlas Academy API取得用のNext.js Route Handler実装（`src/app/api/atlas/servants/route.ts`、`GET /api/atlas/servants`）
  - `GET /export/JP/basic_servant_lang_en.json` を取得し、fgo-apiの `AtlasAcademyClient::isPlayableServant()` と同じ条件でプレイアブルのみに絞る（全413件、Laravelの`servants`テーブルとIDが一致）
  - 一覧表示に必要な項目（id / collectionNo / 英語名 / 日本語名 / クラス / レアリティ / 属性 / 顔画像）だけに絞って返す。`fetch` の `next.revalidate` で1日キャッシュ
  - 型・クラスタブ定義・取得関数は `src/lib/atlas.ts`
【☑️】`/servants` 画面の全サーヴァント一覧表示（図鑑番号順のカードグリッド、日本語名＋レアリティ表示）
【☑️】クラス・レアリティ・名前による絞り込みUI実装
  - レアリティは★0〜★5の複数選択トグル、名前は日本語名・英語名どちらでも部分一致
【☑️】所持済みサーヴァントに「所持済み」チェック表示（`GET /api/servants` との突き合わせ）
  - 所持済みカードは緑の枠＋背景でハイライトし、画像右上に✓バッジを表示
【☑️】未所持サーヴァントに「登録」ボタン表示、押下で `POST /api/servants` 実行
  - `src/lib/api.ts` に `userServantsApi`（list / add / remove）と `Servant` / `UserServant` 型を追加
  - 登録失敗時は422の`servant_id`エラーメッセージ等を画面上部に表示
【☑️】サーヴァント画像クリックで `/servants/[id]` へ遷移（遷移先の画面はチケット0009で実装）
【☑️】クラスタブによる絞り込みUXフローの実装
  - クラスタブ（全クラス＋15クラス。beastEresh / unBeastOlgaMarie等のビースト派生は「ビースト」にまとめる）
  - 画面下部に「手持ちに対応する絆礼装」を表示し、登録に応じて自動更新（データは `GET/POST /api/servants` のレスポンスに含まれる `servant.bond_ce_*` を使用）
  - 「手持ちのみ表示」フィルター
【☑️】ログイン必須ページ共通のヘッダー（ナビ・ユーザー名・ログアウト）を `src/components/app-header.tsx` に切り出し
  - ナビの「所持サーヴァント」（`/my-servants`）はチケット0008で実装
【☑️】`next.config.ts` に `static.atlasacademy.io` の `remotePatterns` を追加。画像枚数が多くVercelの画像最適化の変換上限に当たるため `unoptimized: true`
【☑️】`npm run lint` / `npx tsc --noEmit` / `npm run build` すべて成功を確認
【☑️】Playwright + システムのGoogle Chromeで実ブラウザのE2E確認（スクリプトは確認後に削除）
  - 未ログインで `/servants` → `/login` にガード
  - 全413件表示、セイバー53件 / ビースト3件、★5で198件・★5+★4で335件、「アルトリア」「altria」で各10件
  - マシュ・アルトリアを登録 → ✓所持済み表示、所持数2、絆礼装2件（画像含む）が下部に表示
  - 「手持ちのみ表示」で2件、リロード後も所持状態を保持
  - 画像クリックで `/servants/800100` へ遷移
  - 390px幅（モバイル）で横スクロールなし
  - テストで作成したユーザーはすべて削除済み

## 既知の注意点
- Atlas Academy側にだけ新サーヴァントが追加され、Laravel側の `servants:sync`（週次）がまだ走っていない間は、
  その新サーヴァントの登録が `exists:servants,id` で422になる（エラーメッセージは画面に表示される）。
- Atlas Academyの絆礼装画像（EquipFaces）は150x68の横長画像のため、カードではなく横長で表示している。

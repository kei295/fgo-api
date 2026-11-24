# チケット一覧

`CLAUDE.md` の「Phase 1 の開発ステップ」を元にチケットを分割したもの。
各チケットはこのディレクトリ内に連番のMarkdownファイルとして管理する。

未完了のTODOは `【 】`、完了したTODOは `【☑️】` に書き換えて管理する。

| No | チケット | 状態 |
|---|---|---|
| 0001 | [Laravelプロジェクト作成・Sanctum / CORS設定](0001_setup_sanctum_cors.md) | 【☑️】（本番ドメイン確認は0010待ち） |
| 0002 | [マイグレーション作成（servants / users / user_servants）](0002_migrations.md) | 【☑️】 |
| 0003 | [Atlas Academy APIからのSeeder作成・自動差分同期](0003_servant_seeder_sync.md) | 【☑️】（本番cron設定は0010待ち） |
| 0004 | [認証API実装（register / login / logout）](0004_auth_api.md) | 【☑️】 |
| 0005 | [user_servants API実装（一覧取得・登録・削除）](0005_user_servants_api.md) | 【☑️】 |
| 0006 | [Next.jsプロジェクト作成・認証画面実装](0006_nextjs_setup_auth_screens.md) | 【☑️】 |
| 0007 | [サーヴァント一覧画面実装（/servants）](0007_servant_list_screen.md) | 【☑️】 |
| 0008 | [所持サーヴァント一覧画面実装（/my-servants）](0008_my_servants_screen.md) | 【☑️】 |
| 0009 | [サーヴァント詳細画面実装（/servants/[id]）](0009_servant_detail_screen.md) | 【☑️】 |
| 0010 | [Vercel + Railway へデプロイ](0010_deploy.md) | 【 】 |
| 0011 | [育成状況の編集機能（所持サーヴァント詳細ページ）](0011_growth_status_edit.md) | 【☑️】 |
| 0012 | [サーヴァント一覧ページからの登録解除](0012_unregister_from_list.md) | 【☑️】 |

チケット全体が完了したら上表の「状態」列も `【☑️】` に更新する。

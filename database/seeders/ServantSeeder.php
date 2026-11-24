<?php

namespace Database\Seeders;

use App\Services\AtlasAcademy\AtlasAcademyClient;
use App\Services\AtlasAcademy\ServantSyncService;
use Illuminate\Database\Seeder;

/**
 * Atlas Academy APIから全サーヴァントのマスターデータを取得し、`servants` テーブルへ初回投入する。
 *
 * 実行: php artisan db:seed --class=ServantSeeder
 */
class ServantSeeder extends Seeder
{
    public function __construct(
        private readonly AtlasAcademyClient $client,
        private readonly ServantSyncService $syncService,
    ) {}

    public function run(): void
    {
        $basicList = $this->client->basicServantList();
        $ids = array_column($basicList, 'id');
        $total = count($ids);

        $this->command?->getOutput()->writeln("Atlas Academyから{$total}件のサーヴァントを取得します。");

        $processed = 0;
        foreach (array_chunk($ids, 50) as $chunk) {
            $processed += $this->syncService->syncIds($chunk);
            $this->command?->getOutput()->writeln("  {$processed}/{$total} 件処理済み");
        }

        $this->command?->getOutput()->writeln('ServantSeeder 完了。');
    }
}

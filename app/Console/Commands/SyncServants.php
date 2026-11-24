<?php

namespace App\Console\Commands;

use App\Services\AtlasAcademy\ServantSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('servants:sync')]
#[Description('Atlas Academy APIから新規サーヴァントを検知し、servantsテーブルへ差分同期する')]
class SyncServants extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ServantSyncService $syncService): int
    {
        $this->info('Atlas Academy APIと差分を確認しています...');

        $newIds = $syncService->findNewServantIds();

        if ($newIds === []) {
            $this->info('新規サーヴァントはありません。');

            return self::SUCCESS;
        }

        $this->info(count($newIds).'件の新規サーヴァントを検知しました。取得・登録します。');

        $synced = $syncService->syncIds($newIds);

        $this->info("{$synced}件のサーヴァントを新規登録しました。");

        return self::SUCCESS;
    }
}

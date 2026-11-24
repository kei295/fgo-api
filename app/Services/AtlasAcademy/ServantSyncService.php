<?php

namespace App\Services\AtlasAcademy;

use App\Models\Servant;

/**
 * Atlas Academy APIから取得したサーヴァント詳細を `servants` テーブルへ反映する共通処理。
 * 初回投入（ServantSeeder）と週次の差分同期（servants:sync コマンド）の両方から利用する。
 */
class ServantSyncService
{
    public function __construct(
        private readonly AtlasAcademyClient $client,
        private readonly ServantMapper $mapper,
    ) {}

    /**
     * Atlas Academy上のプレイアブルなサーヴァントIDのうち、`servants` テーブルに未登録のものを返す。
     *
     * @return list<int>
     */
    public function findNewServantIds(): array
    {
        $existingIds = Servant::query()->pluck('id')->all();
        $atlasIds = array_column($this->client->basicServantList(), 'id');

        return array_values(array_diff($atlasIds, $existingIds));
    }

    /**
     * 指定したサーヴァントIDの詳細を取得し、`servants` テーブルへupsertする。
     * 50件ずつチャンクに分けて取得・書き込みを行う。
     *
     * @param  list<int>  $ids
     * @return int 書き込んだ件数
     */
    public function syncIds(array $ids): int
    {
        $count = 0;

        foreach (array_chunk($ids, 50) as $chunk) {
            $count += $this->upsertChunk($chunk);
        }

        return $count;
    }

    /**
     * @param  list<int>  $ids
     */
    private function upsertChunk(array $ids): int
    {
        $details = $this->client->servantDetails($ids);

        if ($details === []) {
            return 0;
        }

        $bondEquipIds = collect($details)->pluck('bondEquip')->filter()->unique()->values()->all();
        $bondCraftEssences = $bondEquipIds !== [] ? $this->client->craftEssenceDetails($bondEquipIds) : [];

        $rows = [];
        foreach ($details as $detail) {
            $bondCe = isset($detail['bondEquip']) ? ($bondCraftEssences[$detail['bondEquip']] ?? null) : null;

            $row = $this->mapper->map($detail, $bondCe);
            $row['ascension_image_urls'] = json_encode($row['ascension_image_urls']);
            $row['created_at'] = now();
            $row['updated_at'] = now();

            $rows[] = $row;
        }

        Servant::upsert($rows, ['id'], array_values(array_diff(array_keys($rows[0]), ['id'])));

        return count($rows);
    }
}

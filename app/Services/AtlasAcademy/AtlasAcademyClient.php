<?php

namespace App\Services\AtlasAcademy;

use Illuminate\Support\Facades\Http;

/**
 * Atlas Academy API（https://api.atlasacademy.io）の読み取り専用クライアント。
 */
class AtlasAcademyClient
{
    private readonly string $baseUrl;

    private readonly string $region;

    private readonly string $lang;

    private readonly int $concurrency;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('atlasacademy.base_url'), '/');
        $this->region = config('atlasacademy.region');
        $this->lang = config('atlasacademy.lang');
        $this->concurrency = max(1, (int) config('atlasacademy.concurrency'));
    }

    /**
     * プレイアブルなサーヴァントの簡易一覧（id / collectionNoのみ必要な差分検知などに使用）。
     *
     * @return list<array{id: int, collection_no: int, name: string}>
     */
    public function basicServantList(): array
    {
        $response = Http::get("{$this->baseUrl}/export/{$this->region}/basic_servant_lang_en.json");
        $response->throw();

        return collect($response->json())
            ->filter(fn (array $entry) => $this->isPlayableServant($entry))
            ->map(fn (array $entry) => [
                'id' => $entry['id'],
                'collection_no' => $entry['collectionNo'],
                'name' => $entry['name'],
            ])
            ->values()
            ->all();
    }

    /**
     * 複数サーヴァントの詳細を並列取得する。
     *
     * @param  list<int>  $ids
     * @return array<int, array> id => detail
     */
    public function servantDetails(array $ids): array
    {
        return $this->fetchManyConcurrently(
            $ids,
            fn (int $id) => "{$this->baseUrl}/nice/{$this->region}/servant/{$id}"
        );
    }

    /**
     * 複数礼装の詳細を並列取得する。
     *
     * @param  list<int>  $ids
     * @return array<int, array> id => detail
     */
    public function craftEssenceDetails(array $ids): array
    {
        return $this->fetchManyConcurrently(
            $ids,
            fn (int $id) => "{$this->baseUrl}/nice/{$this->region}/equip/{$id}"
        );
    }

    /**
     * @param  list<int>  $ids
     * @param  callable(int): string  $urlResolver
     * @return array<int, array> id => response json
     */
    private function fetchManyConcurrently(array $ids, callable $urlResolver): array
    {
        $results = [];

        foreach (array_chunk(array_unique($ids), $this->concurrency) as $chunk) {
            $responses = Http::pool(function ($pool) use ($chunk, $urlResolver) {
                foreach ($chunk as $id) {
                    $pool->as((string) $id)->get($urlResolver($id), ['lang' => $this->lang]);
                }
            });

            foreach ($chunk as $id) {
                $response = $responses[(string) $id];
                if ($response->successful()) {
                    $results[$id] = $response->json();
                }
            }
        }

        return $results;
    }

    private function isPlayableServant(array $entry): bool
    {
        return in_array($entry['type'] ?? null, ['normal', 'heroine'], true)
            && ($entry['flag'] ?? null) === 'normal'
            && ($entry['collectionNo'] ?? 0) > 0;
    }
}

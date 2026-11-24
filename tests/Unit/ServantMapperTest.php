<?php

namespace Tests\Unit;

use App\Services\AtlasAcademy\ServantMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ServantMapperTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function cardTypeProvider(): array
    {
        return [
            'Arts（例: マシュ「ロード・カルデアス」）' => ['1', 'Arts'],
            'Buster（例: アルトリア「約束された勝利の剣」）' => ['2', 'Buster'],
            'Quick（例: メルトリリス「サラスヴァティー・メルトアウト」）' => ['3', 'Quick'],
        ];
    }

    #[DataProvider('cardTypeProvider')]
    public function test_宝具のカードコードを正しいカード種別に変換する(string $card, string $expected): void
    {
        $row = (new ServantMapper)->map($this->servant([
            ['name' => 'NP', 'card' => $card, 'priority' => 100],
        ]), null);

        $this->assertSame($expected, $row['np_type']);
    }

    public function test_宝具はpriorityが最も高いものを採用する(): void
    {
        $row = (new ServantMapper)->map($this->servant([
            ['name' => 'Old NP', 'card' => '1', 'priority' => 101],
            ['name' => 'Strengthened NP', 'card' => '2', 'priority' => 112],
            ['name' => 'Other NP', 'card' => '3', 'priority' => 0],
        ]), null);

        $this->assertSame('Strengthened NP', $row['np_name']);
        $this->assertSame('Buster', $row['np_type']);
    }

    /**
     * @param  list<array{name: string, card: string, priority: int}>  $noblePhantasms
     */
    private function servant(array $noblePhantasms): array
    {
        return [
            'id' => 100100,
            'collectionNo' => 2,
            'name' => 'Altria Pendragon',
            'className' => 'saber',
            'rarity' => 5,
            'attribute' => 'earth',
            'noblePhantasms' => $noblePhantasms,
            'extraAssets' => ['faces' => ['ascension' => ['1' => 'https://example.com/face1.png']]],
        ];
    }
}

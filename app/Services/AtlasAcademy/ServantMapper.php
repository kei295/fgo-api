<?php

namespace App\Services\AtlasAcademy;

/**
 * Atlas Academyの nice servant（＋nice equip）のJSONを `servants` テーブルの行データに変換する。
 */
class ServantMapper
{
    /**
     * カード種別の数値コード → 表記（FGOの内部コード。Atlas Academy nice APIもこの値を返す）。
     * 例: マシュ「ロード・カルデアス」= 1(Arts)、メルトリリス「サラスヴァティー・メルトアウト」= 3(Quick)
     */
    private const CARD_TYPES = [
        '1' => 'Arts',
        '2' => 'Buster',
        '3' => 'Quick',
        '4' => 'Extra',
    ];

    /**
     * 属性の英語表記 → 日本語表記。
     */
    private const ATTRIBUTES = [
        'human' => '人',
        'sky' => '天',
        'earth' => '地',
        'star' => '星',
        'beast' => '獣',
    ];

    /**
     * @param  array  $servant  nice servant のレスポンス
     * @param  array|null  $bondCraftEssence  nice equip のレスポンス（絆礼装が存在する場合）
     * @return array `servants` テーブルの1行分のデータ
     */
    public function map(array $servant, ?array $bondCraftEssence): array
    {
        $noblePhantasm = $this->representativeNoblePhantasm($servant['noblePhantasms'] ?? []);
        $bondEquipId = $servant['bondEquip'] ?? null;

        return [
            'id' => $servant['id'],
            'collection_no' => $servant['collectionNo'],
            'name' => $servant['name'],
            'class_name' => ucfirst($servant['className']),
            'rarity' => $servant['rarity'],
            'attribute' => self::ATTRIBUTES[$servant['attribute']] ?? $servant['attribute'],
            'np_name' => $noblePhantasm['name'] ?? null,
            'np_type' => $noblePhantasm !== null
                ? (self::CARD_TYPES[$noblePhantasm['card']] ?? $noblePhantasm['card'])
                : null,
            'bond_ce_id' => $bondEquipId,
            'bond_ce_name' => $bondCraftEssence['name'] ?? null,
            'face_url' => $servant['extraAssets']['faces']['ascension']['1']
                ?? $servant['face']
                ?? '',
            'ascension_image_urls' => $this->ascensionImageUrls($servant),
            'bond_ce_image_url' => $bondEquipId !== null
                ? ($bondCraftEssence['extraAssets']['equipFace']['equip'][$bondEquipId] ?? null)
                : null,
        ];
    }

    /**
     * 複数の宝具強化段階の中から現在の代表（priorityが最も高いもの）を選ぶ。
     */
    private function representativeNoblePhantasm(array $noblePhantasms): ?array
    {
        if ($noblePhantasms === []) {
            return null;
        }

        usort($noblePhantasms, fn (array $a, array $b) => ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0));

        return $noblePhantasms[0];
    }

    /**
     * 再臨段階（1〜4）ごとの立ち絵（顔画像）URLを、再臨段階順の配列として返す。
     *
     * @return list<string>
     */
    private function ascensionImageUrls(array $servant): array
    {
        $ascensionFaces = $servant['extraAssets']['faces']['ascension'] ?? [];

        ksort($ascensionFaces, SORT_NUMERIC);

        return array_values($ascensionFaces);
    }
}

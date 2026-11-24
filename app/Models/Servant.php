<?php

namespace App\Models;

use Database\Factories\ServantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id',
    'collection_no',
    'name',
    'class_name',
    'rarity',
    'attribute',
    'np_name',
    'np_type',
    'bond_ce_id',
    'bond_ce_name',
    'face_url',
    'ascension_image_urls',
    'bond_ce_image_url',
])]
class Servant extends Model
{
    /** @use HasFactory<ServantFactory> */
    use HasFactory;

    // Atlas AcademyのサーヴァントIDをそのまま主キーとして使用するためauto incrementしない
    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'ascension_image_urls' => 'array',
        ];
    }

    /**
     * @return HasMany<UserServant, $this>
     */
    public function userServants(): HasMany
    {
        return $this->hasMany(UserServant::class);
    }
}

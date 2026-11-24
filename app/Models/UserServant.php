<?php

namespace App\Models;

use Database\Factories\UserServantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'servant_id',
    'level',
    'skill_1',
    'skill_2',
    'skill_3',
    'bond_level',
    'np_level',
    'ascension',
])]
class UserServant extends Model
{
    /** @use HasFactory<UserServantFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Servant, $this>
     */
    public function servant(): BelongsTo
    {
        return $this->belongsTo(Servant::class);
    }
}

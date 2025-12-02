<?php

namespace App\Models\Identity;

use App\Models\System\Database\MetaKey;
use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'user_id',
        'key_id',
        'locale',
        'value',
    ];

    /**
     * 關聯：User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 關聯：MetaKey
     */
    public function metaKey()
    {
        return $this->belongsTo(MetaKey::class, 'key_id');
    }
}

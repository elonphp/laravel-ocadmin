<?php

namespace App\Models\Identity;

use App\Models\System\Database\MetaKey;
use Illuminate\Database\Eloquent\Model;

class AccountMeta extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'account_id',
        'key_id',
        'value',
    ];

    /**
     * 關聯：Account
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * 關聯：MetaKey
     */
    public function metaKey()
    {
        return $this->belongsTo(MetaKey::class, 'key_id');
    }
}

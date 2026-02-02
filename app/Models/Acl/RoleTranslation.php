<?php

namespace App\Models\Acl;

use Illuminate\Database\Eloquent\Model;

class RoleTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'acl_role_translations';

    protected $fillable = [
        'role_id',
        'locale',
        'display_name',
        'note',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}

<?php

namespace App\Models\Acl;

use Illuminate\Database\Eloquent\Model;

class PermissionTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'acl_permission_translations';

    protected $fillable = [
        'permission_id',
        'locale',
        'display_name',
        'note',
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}

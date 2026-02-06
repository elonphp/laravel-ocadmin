<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'organization_translations';

    protected $fillable = [
        'organization_id',
        'locale',
        'name',
        'short_name',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}

<?php

namespace App\Models\Org;

use Illuminate\Database\Eloquent\Model;

class OrganizationTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'org_organization_translations';

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

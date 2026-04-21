<?php

namespace App\Models\Org;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasTranslation;

    protected $table = 'org_organizations';

    protected $fillable = [
        'business_no',
        'shipping_state',
        'shipping_city',
        'shipping_address1',
        'shipping_address2',
    ];

    protected array $translatedAttributes = ['name', 'short_name'];

    protected string $translationModel = OrganizationTranslation::class;

    protected $with = ['translation'];
}

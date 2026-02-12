<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;

class CompanyTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'hrm_company_translations';

    protected $fillable = [
        'company_id',
        'locale',
        'name',
        'short_name',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

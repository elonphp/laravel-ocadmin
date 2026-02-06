<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'company_translations';

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

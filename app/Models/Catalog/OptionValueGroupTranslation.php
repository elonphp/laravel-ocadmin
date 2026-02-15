<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class OptionValueGroupTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'clg_option_value_group_translations';

    protected $fillable = [
        'option_value_group_id',
        'locale',
        'name',
        'description',
    ];
}

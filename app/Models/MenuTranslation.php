<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'sys_menu_translations';

    protected $fillable = [
        'menu_id',
        'locale',
        'display_name',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}

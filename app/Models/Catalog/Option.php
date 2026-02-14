<?php

namespace App\Models\Catalog;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Option extends Model
{
    use HasTranslation;

    protected $table = 'ctl_options';

    protected $fillable = [
        'code',
        'type',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = ['name', 'short_name'];

    /**
     * 選擇型類型（有 option_values）
     */
    public const CHOICE_TYPES = ['select', 'radio', 'checkbox'];

    /**
     * 所有可用類型
     */
    public const TYPES = [
        'select', 'radio', 'checkbox',
        'text', 'textarea',
        'file',
        'date', 'time', 'datetime',
    ];

    public function optionValues(): HasMany
    {
        return $this->hasMany(OptionValue::class)->orderBy('sort_order');
    }

    /**
     * 是否為選擇型（有選項值）
     */
    public function isChoiceType(): bool
    {
        return in_array($this->type, self::CHOICE_TYPES);
    }
}

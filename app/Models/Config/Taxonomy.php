<?php

namespace App\Models\Config;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taxonomy extends Model
{
    use HasTranslation;

    protected $table = 'taxonomies';

    protected $fillable = [
        'code',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = ['name'];

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class)->orderBy('sort_order');
    }

    public function rootTerms(): HasMany
    {
        return $this->hasMany(Term::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}

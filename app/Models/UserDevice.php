<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    protected $table = 'user_devices';

    protected $fillable = [
        'user_id',
        'device_name',
        'device_fingerprint',
        'ip_address',
        'location',
        'last_active_at',
        'is_current',
        'trusted_until',
    ];

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime',
            'trusted_until'  => 'datetime',
            'is_current'     => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isTrusted(): bool
    {
        return $this->trusted_until !== null && $this->trusted_until->isFuture();
    }
}

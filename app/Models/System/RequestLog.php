<?php

namespace App\Models\System;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestLog extends SysdataModel
{
    protected $table = 'request_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'request_trace_id',
        'user_id',
        'app_name',
        'portal',
        'area',
        'url',
        'method',
        'status_code',
        'request_data',
        'response_data',
        'status',
        'note',
        'client_ip',
        'api_ip',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'request_data' => 'array',
        'response_data' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

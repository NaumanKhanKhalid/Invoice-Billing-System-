<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpersonationToken extends Model
{
    public function getConnectionName()
    {
        return config('tenancy.database.central_connection', config('database.default'));
    }

    protected $primaryKey = 'token';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'token', 'tenant_id', 'admin_id', 'admin_name', 'admin_panel_url', 'expires_at',
    ];

    protected $casts = ['expires_at' => 'datetime'];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

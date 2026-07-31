<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'key', 'name', 'price', 'max_users',
        'staff_module', 'google_backup', 'priority_support',
        'is_popular', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price'            => 'integer',
        'max_users'        => 'integer',
        'staff_module'     => 'boolean',
        'google_backup'    => 'boolean',
        'priority_support' => 'boolean',
        'is_popular'       => 'boolean',
        'is_active'        => 'boolean',
    ];

    public function getUnlimitedUsersAttribute(): bool
    {
        return $this->max_users < 0;
    }
}

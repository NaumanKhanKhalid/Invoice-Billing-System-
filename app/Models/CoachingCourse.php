<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachingCourse extends Model
{
    protected $fillable = ['name', 'monthly_fee', 'description', 'is_active'];
    protected $casts    = ['monthly_fee' => 'float', 'is_active' => 'boolean'];

    public function batches(): HasMany
    {
        return $this->hasMany(CoachingBatch::class, 'course_id');
    }
}

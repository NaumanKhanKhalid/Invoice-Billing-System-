<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachingBatch extends Model
{
    protected $fillable = ['course_id', 'name', 'timing', 'days', 'teacher_name', 'capacity', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(CoachingCourse::class, 'course_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(CoachingStudent::class, 'batch_id');
    }

    public function activeStudentsCount(): int
    {
        return $this->students()->where('status', 'active')->count();
    }
}

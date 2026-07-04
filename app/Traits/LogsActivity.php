<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn (Model $model) => static::recordActivity($model, 'created'));
        static::updated(fn (Model $model) => static::recordActivity($model, 'updated'));
        static::deleted(fn (Model $model) => static::recordActivity($model, 'deleted'));
    }

    protected static function recordActivity(Model $model, string $action): void
    {
        try {
            if (!feature_enabled('audit_log')) {
                return;
            }

            $changes = null;
            if ($action === 'updated') {
                $changes = collect($model->getChanges())
                    ->except(['created_at', 'updated_at'])
                    ->map(fn ($new, $key) => ['old' => $model->getOriginal($key), 'new' => $new])
                    ->toArray();
                if (empty($changes)) {
                    return; // nothing meaningful changed
                }
            }

            $user = auth()->user();

            ActivityLog::create([
                'user_id'     => $user?->id,
                'user_name'   => $user?->name,
                'action'      => $action,
                'model_type'  => class_basename($model),
                'model_id'    => $model->getKey(),
                'description' => static::activityDescription($model, $action),
                'changes'     => $changes,
            ]);
        } catch (\Throwable $e) {
            // Logging must never break the main operation (e.g. table not yet migrated).
        }
    }

    protected static function activityDescription(Model $model, string $action): string
    {
        $label = method_exists($model, 'getActivityLabel')
            ? $model->getActivityLabel()
            : class_basename($model) . ' #' . $model->getKey();

        return trim($label . ' ' . $action);
    }
}

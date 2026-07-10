<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class RepairJob extends Model
{
    use LogsActivity;

    protected $fillable = [
        'job_number', 'customer_name', 'customer_phone', 'device', 'serial_imei',
        'fault', 'estimated_cost', 'advance_paid', 'final_cost', 'status',
        'notes', 'ready_at', 'delivered_at',
    ];

    protected $casts = [
        'estimated_cost' => 'float',
        'advance_paid'   => 'float',
        'final_cost'     => 'float',
        'ready_at'       => 'datetime',
        'delivered_at'   => 'datetime',
    ];

    public const STATUSES = ['pending', 'in_progress', 'ready', 'delivered', 'cancelled'];

    public static function nextNumber(): string
    {
        // lockForUpdate is only effective inside a DB::transaction — callers
        // must wrap generate+insert in one to avoid duplicate numbers
        $last = static::orderByDesc('id')->lockForUpdate()->value('job_number');
        $num  = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JOB-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }
}

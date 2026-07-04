<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::orderByDesc('created_at')->orderByDesc('id');

        if ($request->action_filter) $query->where('action', $request->action_filter);
        if ($request->model_type)    $query->where('model_type', $request->model_type);
        if ($request->date)          $query->whereDate('created_at', $request->date);

        $logs = $query->paginate(40)->withQueryString();

        $modelTypes = ActivityLog::distinct()->orderBy('model_type')->pluck('model_type');

        return view('activity-log.index', compact('logs', 'modelTypes'));
    }
}

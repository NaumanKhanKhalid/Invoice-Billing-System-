<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogViewerController extends Controller
{
    /**
     * Super-admin error log viewer — the last N entries of the newest
     * laravel log file, grouped into entries, errors highlighted.
     */
    public function index(Request $request)
    {
        $files = glob(storage_path('logs/laravel*.log')) ?: [];
        rsort($files); // newest first (daily logs are date-suffixed)

        $selected = $request->input('file');
        $file     = ($selected && in_array(storage_path('logs/' . basename($selected)), $files))
            ? storage_path('logs/' . basename($selected))
            : ($files[0] ?? null);

        $entries = [];
        if ($file && is_readable($file)) {
            // Read only the tail (last ~200KB) so huge logs don't blow memory
            $size   = filesize($file);
            $handle = fopen($file, 'r');
            if ($size > 204800) {
                fseek($handle, -204800, SEEK_END);
                fgets($handle); // drop the partial first line
            }
            $content = stream_get_contents($handle);
            fclose($handle);

            // Split on the [YYYY-MM-DD HH:MM:SS] entry marker
            $parts = preg_split('/(?=^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/m', $content, -1, PREG_SPLIT_NO_EMPTY);
            foreach (array_reverse($parts) as $part) {
                if (count($entries) >= 100) break;
                preg_match('/^\[(?<time>[^\]]+)\]\s+(?<env>\w+)\.(?<level>\w+):\s?(?<message>.*)/s', $part, $m);
                $entries[] = [
                    'time'    => $m['time'] ?? '',
                    'level'   => strtoupper($m['level'] ?? 'INFO'),
                    'message' => trim($m['message'] ?? $part),
                ];
            }
        }

        $fileNames = array_map('basename', $files);

        return view('admin.logs', [
            'entries'  => $entries,
            'files'    => $fileNames,
            'current'  => $file ? basename($file) : null,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;

class DataExportController extends Controller
{
    /**
     * Tables included in the tenant data export.
     */
    protected array $tables = [
        'products',
        'pos_sales',
        'pos_sale_items',
        'credit_sales',
        'credit_payments',
        'udhar_customers',
        'expenses',
        'staff',
        'salary_payments',
        'suppliers',
        'customers',
        'purchase_orders',
        'supply_orders',
        'open_tabs',
        'open_tab_items',
        'quotations',
        'quotation_items',
        'coaching_courses',
        'coaching_batches',
        'coaching_students',
        'coaching_fee_collections',
        'day_summaries',
        'daily_records',
    ];

    public function download()
    {
        // Tenancy suffixes storage_path() per tenant and that dir may not
        // exist yet; use the system temp dir which always does
        $zipPath = sys_get_temp_dir() . '/export-' . uniqid() . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create export archive.');
        }

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $zip->addFromString($table . '.csv', $this->tableToCsv($table));
        }

        $zip->close();

        $slug = Str::slug(tenant()->shop_name ?? 'shop') ?: 'shop';
        $filename = $slug . '-data-export-' . now()->format('Y-m-d') . '.zip';

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Build a UTF-8 CSV string (header + all rows) for the given table.
     */
    protected function tableToCsv(string $table): string
    {
        $columns = Schema::getColumnListing($table);

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $columns);

        DB::table($table)->orderBy($columns[0])->chunk(500, function ($rows) use ($handle, $columns) {
            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $column) {
                    $line[] = $row->{$column};
                }
                fputcsv($handle, $line);
            }
        });

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}

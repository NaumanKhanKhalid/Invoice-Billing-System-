<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Console\Command;

class BackupToGoogleDrive extends Command
{
    protected $signature   = 'backup:google';
    protected $description = 'Backup database to Google Drive';

    public function handle(): int
    {
        $tokenJson = Setting::getValue('google_drive_token');

        if (!$tokenJson) {
            $this->error('Google Drive not connected. Please connect from Settings page first.');
            return 1;
        }

        $client = new Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->addScope(Drive::DRIVE_FILE);
        $client->setAccessType('offline');

        $token = json_decode($tokenJson, true);
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            if (!$client->getRefreshToken()) {
                $this->error('Google Drive token expired and no refresh token. Please reconnect from Settings.');
                return 1;
            }
            $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            Setting::setValue('google_drive_token', json_encode($newToken));
            $client->setAccessToken($newToken);
        }

        $service   = new Drive($client);
        $folderId  = $this->getOrCreateFolder($service, 'Anwar Chicken Backups');
        $timestamp = now()->format('Y-m-d_H-i-s');

        // Build dump using PHP PDO (no external commands needed)
        $connection = config('database.default');
        if ($connection === 'mysql') {
            $host    = config('database.connections.mysql.host', '127.0.0.1');
            $port    = config('database.connections.mysql.port', '3306');
            $db      = config('database.connections.mysql.database');
            $user    = config('database.connections.mysql.username');
            $pass    = config('database.connections.mysql.password');
            $content = $this->phpMysqlDump($host, $port, $db, $user, $pass);
            if (!$content) {
                $this->error('Database dump failed. Check DB credentials in .env');
                return 1;
            }
            $backupName = "backup_{$timestamp}.sql";
        } else {
            $dbPath = config('database.connections.sqlite.database', database_path('database.sqlite'));
            if (!file_exists($dbPath)) {
                $this->error('Database file not found at: ' . $dbPath);
                return 1;
            }
            $content    = file_get_contents($dbPath);
            $backupName = "backup_{$timestamp}.sqlite";
        }

        $fileMetadata = new DriveFile([
            'name'    => $backupName,
            'parents' => [$folderId],
        ]);

        $service->files->create($fileMetadata, [
            'data'       => $content,
            'mimeType'   => 'application/octet-stream',
            'uploadType' => 'multipart',
            'fields'     => 'id,name',
        ]);

        Setting::setValue('last_backup_at', now()->toDateTimeString());

        $this->info("Backup '{$backupName}' uploaded to Google Drive successfully.");
        return 0;
    }

    private function getOrCreateFolder(Drive $service, string $name): string
    {
        $results = $service->files->listFiles([
            'q'      => "mimeType='application/vnd.google-apps.folder' and name='{$name}' and trashed=false",
            'fields' => 'files(id)',
        ]);

        if (count($results->getFiles()) > 0) {
            return $results->getFiles()[0]->getId();
        }

        $folder  = new DriveFile(['name' => $name, 'mimeType' => 'application/vnd.google-apps.folder']);
        $created = $service->files->create($folder, ['fields' => 'id']);
        return $created->getId();
    }

    private function phpMysqlDump(string $host, string $port, string $db, string $user, string $pass): ?string
    {
        try {
            $pdo    = new \PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8", $user, $pass);
            $output = "-- Anwar Chicken Center Database Backup\n-- Date: " . now() . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $create  = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $output .= $create['Create Table'] . ";\n\n";
                $rows    = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $vals    = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote($v), $row);
                    $cols    = '`' . implode('`, `', array_keys($row)) . '`';
                    $output .= "INSERT INTO `{$table}` ({$cols}) VALUES (" . implode(', ', $vals) . ");\n";
                }
                $output .= "\n";
            }
            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
            return $output;
        } catch (\Exception $e) {
            return null;
        }
    }
}

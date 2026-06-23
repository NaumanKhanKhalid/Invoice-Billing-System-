<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveController extends Controller
{
    private function getClient(): Client
    {
        $client = new Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->addScope(Drive::DRIVE_FILE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        return $client;
    }

    public function connect()
    {
        $client = $this->getClient();
        return redirect($client->createAuthUrl());
    }

    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect()->route('settings.index')->with('error', 'Google connection cancelled.');
        }

        $client = $this->getClient();
        $token  = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return redirect()->route('settings.index')->with('error', 'Google error: ' . $token['error_description']);
        }

        Setting::setValue('google_drive_token', json_encode($token));

        return redirect()->route('settings.index')->with('success', 'Google Drive connected successfully!');
    }

    public function disconnect()
    {
        Setting::setValue('google_drive_token', null);
        return redirect()->route('settings.index')->with('success', 'Google Drive disconnected.');
    }

    public function backup()
    {
        $tokenJson = Setting::getValue('google_drive_token');
        if (!$tokenJson) {
            return redirect()->route('settings.index')->with('error', 'Google Drive not connected. Please connect first.');
        }

        $client = $this->getAuthenticatedClient($tokenJson);
        if (!$client) {
            return redirect()->route('settings.index')->with('error', 'Google Drive session expired. Please reconnect.');
        }

        // Find or create backup folder
        $service    = new Drive($client);
        $folderId   = $this->getOrCreateFolder($service, 'Anwar Chicken Backups');

        [$backupName, $content, $error] = $this->createDatabaseDump();
        if ($error) {
            return redirect()->route('settings.index')->with('error', $error);
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

        return redirect()->route('settings.index')->with('success', 'Backup "' . $backupName . '" Google Drive mein save ho gaya!');
    }

    public function listBackups()
    {
        $tokenJson = Setting::getValue('google_drive_token');
        if (!$tokenJson) {
            return redirect()->route('settings.index')->with('error', 'Google Drive not connected.');
        }

        $client = $this->getAuthenticatedClient($tokenJson);
        if (!$client) {
            return redirect()->route('settings.index')->with('error', 'Google Drive session expired. Please reconnect.');
        }

        $service  = new Drive($client);
        $folderId = $this->getOrCreateFolder($service, 'Anwar Chicken Backups');

        $results = $service->files->listFiles([
            'q'       => "'{$folderId}' in parents and trashed=false",
            'fields'  => 'files(id,name,size,createdTime)',
            'orderBy' => 'createdTime desc',
        ]);

        $backups = collect($results->getFiles())->map(fn($f) => [
            'id'      => $f->getId(),
            'name'    => $f->getName(),
            'size'    => round($f->getSize() / 1024, 1) . ' KB',
            'created' => \Carbon\Carbon::parse($f->getCreatedTime())->format('d M Y, h:i A'),
        ]);

        return view('settings.backups', compact('backups'));
    }

    public function restore(string $fileId)
    {
        $tokenJson = Setting::getValue('google_drive_token');
        if (!$tokenJson) {
            return redirect()->route('settings.index')->with('error', 'Google Drive not connected.');
        }

        $client = $this->getAuthenticatedClient($tokenJson);
        if (!$client) {
            return redirect()->route('settings.index')->with('error', 'Google Drive session expired. Please reconnect.');
        }

        $service  = new Drive($client);
        $response = $service->files->get($fileId, ['alt' => 'media']);
        $content  = $response->getBody()->getContents();

        $connection = config('database.default');

        if ($connection === 'mysql') {
            $host = config('database.connections.mysql.host', '127.0.0.1');
            $port = config('database.connections.mysql.port', '3306');
            $db   = config('database.connections.mysql.database');
            $user = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');

            try {
                $pdo = new \PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8", $user, $pass);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                // Split SQL into individual statements and execute
                $statements = array_filter(
                    array_map('trim', explode(";\n", $content)),
                    fn($s) => !empty($s) && !str_starts_with($s, '--')
                );

                $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
                foreach ($statements as $sql) {
                    if (!empty(trim($sql))) {
                        try { $pdo->exec($sql); } catch (\Exception $e) { /* skip errors on individual statements */ }
                    }
                }
                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $e) {
                return redirect()->route('settings.backups')->with('error', 'Restore failed: ' . $e->getMessage());
            }
        } else {
            $dbPath = config('database.connections.sqlite.database', database_path('database.sqlite'));
            copy($dbPath, $dbPath . '.before_restore');
            file_put_contents($dbPath, $content);
        }

        return redirect()->route('settings.backups')->with('success', 'Database restore ho gaya! Data wapis aa gaya.');
    }

    public function deleteBackup(string $fileId)
    {
        $tokenJson = Setting::getValue('google_drive_token');
        if (!$tokenJson) {
            return redirect()->route('settings.backups')->with('error', 'Google Drive not connected.');
        }

        $client = $this->getAuthenticatedClient($tokenJson);
        if (!$client) {
            return redirect()->route('settings.backups')->with('error', 'Session expired. Please reconnect.');
        }

        $service = new Drive($client);
        $service->files->delete($fileId);

        return redirect()->route('settings.backups')->with('success', 'Backup delete ho gaya.');
    }

    private function getAuthenticatedClient(string $tokenJson): ?Client
    {
        $client = $this->getClient();
        $token  = json_decode($tokenJson, true);
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            if (!$client->getRefreshToken()) {
                Setting::setValue('google_drive_token', null);
                return null;
            }
            $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            Setting::setValue('google_drive_token', json_encode($newToken));
            $client->setAccessToken($newToken);
        }

        return $client;
    }

    private function createDatabaseDump(): array
    {
        $connection = config('database.default');
        $timestamp  = now()->format('Y-m-d_H-i-s');

        if ($connection === 'mysql') {
            $host    = config('database.connections.mysql.host', '127.0.0.1');
            $port    = config('database.connections.mysql.port', '3306');
            $db      = config('database.connections.mysql.database');
            $user    = config('database.connections.mysql.username');
            $pass    = config('database.connections.mysql.password');
            $content = $this->phpMysqlDump($host, $port, $db, $user, $pass);
            if (!$content) {
                return ['', '', 'Database dump failed. Check DB credentials in .env'];
            }
            return ["backup_{$timestamp}.sql", $content, null];
        }

        $dbPath = config('database.connections.sqlite.database', database_path('database.sqlite'));
        if (!file_exists($dbPath)) {
            return ['', '', 'Database file not found at: ' . $dbPath];
        }
        return ["backup_{$timestamp}.sqlite", file_get_contents($dbPath), null];
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

                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
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

    private function getOrCreateFolder(Drive $service, string $name): string
    {
        // Check if folder exists
        $results = $service->files->listFiles([
            'q'      => "mimeType='application/vnd.google-apps.folder' and name='{$name}' and trashed=false",
            'fields' => 'files(id)',
        ]);

        if (count($results->getFiles()) > 0) {
            return $results->getFiles()[0]->getId();
        }

        // Create folder
        $folder = new DriveFile([
            'name'     => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);
        $created = $service->files->create($folder, ['fields' => 'id']);
        return $created->getId();
    }
}

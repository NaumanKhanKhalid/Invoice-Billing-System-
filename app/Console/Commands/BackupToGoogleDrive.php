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

        $service    = new Drive($client);
        $folderId   = $this->getOrCreateFolder($service, 'Anwar Chicken Backups');
        $dbPath     = database_path('database.sqlite');
        $backupName = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sqlite';

        $fileMetadata = new DriveFile([
            'name'    => $backupName,
            'parents' => [$folderId],
        ]);

        $service->files->create($fileMetadata, [
            'data'       => file_get_contents($dbPath),
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
}

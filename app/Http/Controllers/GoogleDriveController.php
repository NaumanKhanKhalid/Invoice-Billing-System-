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

        // Create backup file
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
            'q'       => "'{$folderId}' in parents and trashed=false and mimeType='application/octet-stream'",
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

        $dbPath = database_path('database.sqlite');

        // Keep a safety copy of current DB before restore
        copy($dbPath, $dbPath . '.before_restore');

        file_put_contents($dbPath, $content);

        return redirect()->route('settings.backups')->with('success', 'Database restore ho gaya! Previous data wapis aa gaya.');
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

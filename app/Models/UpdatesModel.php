<?php

namespace App\Models;

use CodeIgniter\Model;

class UpdatesModel extends Model
{
    private string $cacheVar = 'updates-info';

    public function fetchInfo(): ?array
    {
        $ch = curl_init(DOWNLOADS_URL . '/' . PRODUCT_ID . '.json');

        curl_setopt_array($ch, [
            CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'] ?? 'Salon Migration',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
        ]);

        $contents = curl_exec($ch);
        curl_close($ch);

        $info = json_decode((string) $contents, true);

        return is_array($info) ? $info : null;
    }

    public function isUploaded(): bool
    {
        $cache = service('cache');
        $uploaded = $cache->get('is-update-uploaded');

        if ($uploaded === null) {
            $updatePath = APPPATH . 'ThirdParty/update/';
            $uploaded = is_file($updatePath . 'upload.zip') && is_file($updatePath . 'update.json');
            $cache->save('is-update-uploaded', $uploaded, 300);
        }

        return (bool) $uploaded;
    }

    public function updateInfo(): array
    {
        $return = [
            'uploaded' => $this->isUploaded(),
            'status' => 'available',
        ];

        $cache = service('cache');
        $info = $cache->get($this->cacheVar);

        if ($info === null) {
            $info = $this->fetchInfo();
            $cache->save($this->cacheVar, $info ?? [], 3600);
        }

        if (! is_array($info) || ! isset($info['version'])) {
            $return['status'] = 'unavailable';

            return $return;
        }

        if ((string) $info['version'] === (string) PRODUCT_VERSION) {
            $return['status'] = 'latest';
        }

        return $return;
    }
}

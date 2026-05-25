<?php

namespace App\Models;

use CodeIgniter\Model;

class SocialKeysModel extends Model
{
    protected $table = 'social-keys-settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'google_public',
        'google_secret',
        'facebook_public',
        'facebook_secret',
    ];

    public function get(): array
    {
        $row = $this->first() ?? [];
        $row['google_status'] = ! empty($row['google_secret']) && ! empty($row['google_public']);
        $row['facebook_status'] = ! empty($row['facebook_secret']) && ! empty($row['facebook_public']);

        return $row;
    }

    public function updateSettings(array $fields): bool
    {
        return $this->update(1, $fields);
    }
}

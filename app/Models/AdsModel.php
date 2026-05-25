<?php

namespace App\Models;

use CodeIgniter\Model;

class AdsModel extends Model
{
    protected $table = 'ad-settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['top_ad_status', 'top_ad', 'bottom_ad_status', 'bottom_ad'];

    public function get(): array
    {
        $row = $this->first() ?? [];

        return [
            'top' => [
                'status' => $row['top_ad_status'] ?? 0,
                'code' => $row['top_ad'] ?? '',
            ],
            'bottom' => [
                'status' => $row['bottom_ad_status'] ?? 0,
                'code' => $row['bottom_ad'] ?? '',
            ],
        ];
    }

    public function updateSettings(array $fields): bool
    {
        return $this->update(1, $fields);
    }
}

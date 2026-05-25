<?php

namespace App\Models;

use CodeIgniter\Model;

class AnalyticsModel extends Model
{
    protected $table = 'analytics-settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['code'];

    public function get(): string
    {
        $row = $this->first();

        return (string) ($row['code'] ?? '');
    }

    public function updateSettings(array $fields): bool
    {
        return $this->update(1, $fields);
    }
}

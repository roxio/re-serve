<?php

namespace App\Models;

use CodeIgniter\Model;

class GeneralModel extends Model
{
    protected $table = 'general-settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'title',
        'description',
        'keywords',
        'logo',
        'favicon',
    ];

    public function get(): ?array
    {
        return $this->first();
    }

    public function updateSettings($fields): bool
    {
        return $this->update(1, $fields);
    }
}

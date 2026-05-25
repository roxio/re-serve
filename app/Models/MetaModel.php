<?php

namespace App\Models;

use CodeIgniter\Model;

class MetaModel extends Model
{
    protected $table = 'meta-tags-settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['meta_tags'];

    public function get()
    {
        $row = $this->first();

        return $row['meta_tags'] ?? false;
    }

    public function updateSettings(array $fields): bool
    {
        return $this->update(1, $fields);
    }
}

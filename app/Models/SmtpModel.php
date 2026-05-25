<?php

namespace App\Models;

use CodeIgniter\Model;

class SmtpModel extends Model
{
    protected $table = 'smtp-settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['email', 'status', 'host', 'port', 'username', 'password'];

    public function get(): ?array
    {
        return $this->first();
    }

    public function updateSettings(array $fields): bool
    {
        return $this->update(1, $fields);
    }
}

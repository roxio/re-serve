<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientsModel extends Model
{
    protected $table = 'logintbl';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'fullName',
        'email',
        'phone',
    ];

    public function get(): array
    {
        return $this->where('role', 0)->orderBy('id', 'asc')->findAll();
    }

    public function getclient($id): ?array
    {
        return $this->where('role', 0)->find($id);
    }

    public function updateClient($id, array $fields): bool
    {
        return $this->update($id, $fields);
    }

    public function deleteclient($id): bool
    {
        return $this->delete($id);
    }
}

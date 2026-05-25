<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentsModel extends Model
{
    protected $table = 'agents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'agentName',
        'agentDetail',
        'experience',
        'totalBookings',
        'agentImage',
    ];

    public function agentList(): array
    {
        return $this->orderBy('id', 'desc')->findAll();
    }

    public function addAgent(array $data): bool
    {
        return (bool) $this->insert($data);
    }

    public function getAgent($id): ?array
    {
        return $this->find($id);
    }

    public function updateAgent($id, array $fields): bool
    {
        return $this->update($id, $fields);
    }

    public function deleteAgent($id): bool
    {
        return $this->delete($id);
    }
}

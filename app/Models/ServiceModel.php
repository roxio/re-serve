<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'servicetable';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'title',
        'description',
        'price',
        'servSpace',
        'servStart',
        'servEnd',
        'servDuration',
        'agentIds',
        'image',
    ];

    public function serviceList(): array
    {
        return $this->orderBy('id', 'desc')->findAll();
    }

    public function agentListByService(): array
    {
        $services = $this->orderBy('id', 'desc')->findAll();

        foreach ($services as $index => $service) {
            $agentIds = array_filter(array_map('trim', explode(',', (string) ($service['agentIds'] ?? ''))));
            $service['agentIds'] = [];

            foreach ($agentIds as $id) {
                $agent = $this->db->table('agents')->where('id', $id)->get(1)->getRowArray();

                if ($agent) {
                    $service['agentIds'][] = $agent;
                }
            }

            $services[$index] = $service;
        }

        return $services;
    }

    public function addService(array $data): bool
    {
        return (bool) $this->insert($data);
    }

    public function getservice($id): ?array
    {
        return $this->find($id);
    }

    public function updateService($id, array $fields): bool
    {
        return $this->update($id, $fields);
    }

    public function deleteService($id): bool
    {
        return $this->delete($id);
    }

    public function servicedataById($id): ?array
    {
        return $this->where('id', $id)->first();
    }

    public function selectAgents($agentIds): array
    {
        $ids = array_filter(array_map('trim', explode(',', (string) $agentIds)));

        if ($ids === []) {
            return [];
        }

        return $this->db
            ->table('agents')
            ->whereIn('id', $ids)
            ->get()
            ->getResultArray();
    }
}

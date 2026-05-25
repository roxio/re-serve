<?php

namespace App\Models;

use CodeIgniter\Model;

class BlogModel extends Model
{
    protected $table = 'blog';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'title',
        'description',
        'image',
        'status',
        'permalink',
        'datetime_added',
        'datetime_updated',
    ];

    public function blogStatus(): ?array
    {
        return $this->db
            ->table('blogstatus')
            ->limit(1)
            ->get()
            ->getRowArray();
    }

    public function blogStatusSet(array $fields): bool
    {
        return $this->db->table('blogstatus')->where('id', 1)->update($fields);
    }

    public function blogList(): array
    {
        return $this->orderBy('id', 'desc')->findAll();
    }

    public function blogListu(int $limit, int $offset): array
    {
        return $this->orderBy('id', 'desc')
            ->findAll($limit, $offset);
    }

    public function num_rows(): int
    {
        return $this->countAllResults();
    }

    public function get_post_by_permalink($permalink): ?array
    {
        return $this->where('permalink', strtolower((string) $permalink))
            ->first();
    }

    public function get_post($id): ?array
    {
        return $this->find($id);
    }

    public function checkPermalink($permalink): int
    {
        return $this->where('permalink', strtolower((string) $permalink))->countAllResults();
    }

    public function add_post(array $fields): bool
    {
        return (bool) $this->insert($fields);
    }

    public function update_post($id, array $fields): bool
    {
        return $this->update($id, $fields);
    }

    public function delete_post($id): bool
    {
        return $this->delete($id);
    }

    public function commentSettings(): ?array
    {
        return $this->db
            ->table('comments-settings')
            ->limit(1)
            ->get()
            ->getRowArray();
    }
}

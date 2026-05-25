<?php

namespace App\Models;

use CodeIgniter\Model;

class PageModel extends Model
{
    protected $table = 'pages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'title',
        'content',
        'permalink',
        'position',
        'status',
        'page_order',
    ];

    public function get(): array
    {
        return $this->select('page_order, position, status, permalink, title')
            ->orderBy('page_order', 'asc')
            ->findAll();
    }

    public function set_order($arrayPermalinks): bool
    {
        foreach ($arrayPermalinks as $order => $page) {
            if (! $this->where('permalink', $page)->set(['page_order' => $order])->update()) {
                return false;
            }
        }

        return true;
    }

    public function get_new_page_order(): int
    {
        $page = $this->select('page_order')->orderBy('page_order', 'desc')->first();

        return (int) ($page['page_order'] ?? 0) + 1;
    }

    public function get_page($permalink): ?array
    {
        return $this->where('permalink', strtolower((string) $permalink))->first();
    }

    public function create_page($insert): bool
    {
        return (bool) $this->insert($insert);
    }

    public function delete_page($permalink): bool
    {
        $deleted = $this->where('permalink', strtolower((string) $permalink))->delete();
        $pages = $this->get();

        foreach ($pages as $order => $page) {
            $this->where('permalink', $page['permalink'])->set(['page_order' => $order])->update();
        }

        return (bool) $deleted;
    }

    public function set_page($permalink, $fields): bool
    {
        return $this->where('permalink', strtolower((string) $permalink))->set($fields)->update();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryModel extends Model
{
    protected $table = 'gallery';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'imgName',
        'imgDetails',
        'catId',
        'imgPath',
    ];

    public function listCat(): array
    {
        $categories = $this->db
            ->table('gcategory')
            ->orderBy('id', 'asc')
            ->get()
            ->getResultArray();

        foreach ($categories as $index => $category) {
            $categories[$index]['count'] = $this->db
                ->table('gallery')
                ->where('catId', $category['id'])
                ->countAllResults();
        }

        return $categories;
    }

    public function listGallery(): array
    {
        return $this->orderBy('id', 'asc')->findAll();
    }

    public function listGalleryWidCat(): array
    {
        return $this->db
            ->table('gcategory')
            ->select('gallery.*, gcategory.cName')
            ->join('gallery', 'gcategory.id = gallery.catId', 'left')
            ->where('gcategory.id !=', null)
            ->where('gallery.catId !=', null)
            ->orderBy('gallery.id', 'desc')
            ->get()
            ->getResultArray();
    }

    public function setCat(array $data): bool
    {
        return $this->db->table('gcategory')->insert($data);
    }

    public function getCat($id): ?array
    {
        return $this->db->table('gcategory')->where('id', $id)->get(1)->getRowArray();
    }

    public function updateCat($id, array $fields): bool
    {
        return $this->db->table('gcategory')->where('id', $id)->update($fields);
    }

    public function deleteCat($id): bool
    {
        return $this->db->table('gcategory')->where('id', $id)->delete();
    }

    public function getGallery($id): ?array
    {
        return $this->find($id);
    }

    public function setGallery(array $data): bool
    {
        return (bool) $this->insert($data);
    }

    public function updateGallery($id, array $fields): bool
    {
        return $this->update($id, $fields);
    }

    public function deleteGallery($id): bool
    {
        return $this->delete($id);
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactDetails extends Model
{
    protected $table = 'contactdetails';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'phone',
        'email',
        'address',
        'map_src',
        'map_wd',
        'map_ht',
        'urlFb',
        'urlTwt',
        'urlIn',
    ];

    public function get(): ?array
    {
        return $this->first();
    }

    public function getDetails(): ?array
    {
        return $this->first();
    }

    public function updateDetails($fields): bool
    {
        return $this->update(1, $fields);
    }
}

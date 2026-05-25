<?php

namespace App\Models;

use CodeIgniter\Model;

class RecaptchaModel extends Model
{
    protected $table = 'recaptcha-settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['status', 'site_key', 'secret_key'];

    public function get(): ?array
    {
        return $this->first();
    }

    public function updateSettings(array $fields): bool
    {
        return $this->update(1, $fields);
    }
}

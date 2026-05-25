<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentSettingsModel extends Model
{
    protected $table = 'comments-settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['active_plugin', 'facebook_app_id', 'disqus_short_name'];

    public function get(): ?array
    {
        return $this->first();
    }

    public function updateSettings(array $fields): bool
    {
        return $this->update(1, $fields);
    }
}

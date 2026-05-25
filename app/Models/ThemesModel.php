<?php

namespace App\Models;

use CodeIgniter\Model;

class ThemesModel extends Model
{
    protected $table = 'themesettings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['theme'];

    public function get(): string
    {
        $row = $this->first();

        return $row['theme'] ?? 'redishtheme';
    }

    public function doesThemeExist($theme)
    {
        $manifest = APPPATH . 'Views/themes/' . trim((string) $theme) . '/manifest.json';

        return is_file($manifest) ? json_decode((string) file_get_contents($manifest), true) : false;
    }

    public function getAvailableThemes(): array
    {
        $themeDirs = array_filter(glob(APPPATH . 'Views/themes/*') ?: [], 'is_dir');
        $themes = [];

        foreach ($themeDirs as $theme) {
            $manifestPath = $theme . '/manifest.json';

            if (! is_file($manifestPath)) {
                continue;
            }

            $manifest = json_decode((string) file_get_contents($manifestPath), true);

            if (! is_array($manifest)) {
                continue;
            }

            $identifier = basename($theme);
            $manifest['identifier'] = $identifier;

            $themes[] = [
                'manifest' => $manifest,
                'cover' => base_url('assets/theme/' . $identifier . '/' . ltrim((string) ($manifest['cover'] ?? ''), '/')),
                'thumbnail' => base_url('assets/theme/' . $identifier . '/' . ltrim((string) ($manifest['thumbnail'] ?? ''), '/')),
            ];
        }

        return $themes;
    }

    public function updateSettings($fields): bool
    {
        return $this->update(1, $fields);
    }
}

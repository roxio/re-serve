<?php

namespace App\Models;

use CodeIgniter\Model;

class MainModel extends Model
{
    protected $table = 'general-settings';

    public function admin_settings(): ?array
    {
        $session = session();
        $adminId = $session->get('admin_id');

        if (! $adminId) {
            return null;
        }

        return [
            'id' => $adminId,
            'fullName' => $session->get('admin_fullName'),
            'email' => $session->get('admin_email'),
            'role' => $session->get('admin_role'),
            'disabled' => DEMO_MODE,
        ];
    }

    public function general_settings(): ?array
    {
        return $this->firstRow('general-settings');
    }

    public function ads_settings(): array
    {
        $row = $this->firstRow('ad-settings') ?? [];

        return [
            'top' => [
                'status' => $row['top_ad_status'] ?? 0,
                'code' => $row['top_ad'] ?? '',
            ],
            'bottom' => [
                'status' => $row['bottom_ad_status'] ?? 0,
                'code' => $row['bottom_ad'] ?? '',
            ],
        ];
    }

    public function meta_settings()
    {
        $row = $this->firstRow('meta-tags-settings');

        return $row['meta_tags'] ?? false;
    }

    public function analytics_settings(): string
    {
        $row = $this->firstRow('analytics-settings');

        return (string) ($row['code'] ?? '');
    }

    public function smtp_settings(): ?array
    {
        return $this->firstRow('smtp-settings');
    }

    public function comment_settings(): ?array
    {
        return $this->firstRow('comments-settings');
    }

    public function all_pages(): array
    {
        return $this->db
            ->table('pages')
            ->select('page_order, position, status, permalink, title')
            ->orderBy('page_order', 'asc')
            ->get()
            ->getResultArray();
    }

    public function recaptcha_settings(): ?array
    {
        return $this->firstRow('recaptcha-settings');
    }

    public function updates_settings(): array
    {
        return [
            'version' => PRODUCT_VERSION,
            'product' => PRODUCT_NAME,
            'status' => 'none',
        ];
    }

    public function theme(): string
    {
        $row = $this->firstRow('themesettings');

        return $row['theme'] ?? 'redishtheme';
    }

    public function theme_view(array $baseData = []): callable
    {
        $theme = $this->theme();

        return static function ($view, $data = []) use ($theme, $baseData) {
            echo view('themes/' . $theme . '/' . $view, array_merge($baseData, $data ?? []));

            return true;
        };
    }

    public function theme_assets(): callable
    {
        $theme = $this->theme();

        return static function ($path) use ($theme) {
            echo base_url('assets/theme/' . $theme . '/' . ltrim((string) $path, '/'));

            return true;
        };
    }

    public function social_keys(): array
    {
        $row = $this->firstRow('social-keys-settings') ?? [];

        $row['google_status'] = ! empty($row['google_secret']) && ! empty($row['google_public']);
        $row['facebook_status'] = ! empty($row['facebook_secret']) && ! empty($row['facebook_public']);

        return $row;
    }

    public function pageData(): array
    {
        $settings = [
            'general' => $this->general_settings(),
            'meta_tags' => $this->meta_settings(),
            'ads' => $this->ads_settings(),
            'recaptcha' => $this->recaptcha_settings(),
            'analytics' => $this->analytics_settings(),
            'pages' => $this->all_pages(),
            'theme' => $this->theme(),
            'blogStatus' => $this->blogStatus(),
        ];

        $theme = $settings['theme'];

        $settings['theme_view'] = static function ($view, $data = []) use (&$settings, $theme) {
            echo view('themes/' . $theme . '/' . $view, array_merge($settings, $data ?? []));

            return true;
        };

        $settings['assets'] = static function ($path) use ($theme) {
            echo base_url('assets/theme/' . $theme . '/' . ltrim((string) $path, '/'));

            return true;
        };

        return $settings;
    }

    private function blogStatus(): ?array
    {
        return $this->firstRow('blogstatus');
    }

    private function firstRow(string $table): ?array
    {
        return $this->db
            ->table($table)
            ->limit(1)
            ->get()
            ->getRowArray();
    }
}

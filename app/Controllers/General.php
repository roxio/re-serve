<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\BookingModel;
use App\Models\GeneralModel;
use App\Models\LoginModel;
use App\Models\MainModel;
use App\Models\ThemesModel;
use ZipArchive;

class General extends BaseController
{
    private array $pageData;
    private ?array $adminUser;

    public function __construct()
    {
        $mainModel = new MainModel();
        $adminModel = new AdminModel();

        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->adminUser = $adminModel->adminDetails();
    }

    public function index()
    {
        return redirect()->to(base_url(GENERAL_CONTROLLER . '/dashboard'));
    }

    public function dashboard()
    {
        $loginModel = new LoginModel();
        $bookingModel = new BookingModel();

        $this->pageData['clients'] = $loginModel->recent_registrations(10);
        $this->pageData['weekly_users'] = $loginModel->weekly_registrations();
        $this->pageData['total_users'] = $loginModel->total_registrations();
        $this->pageData['recent_bookings'] = $bookingModel->recent_bookings(10);
        $this->pageData['weekly_bookings'] = $bookingModel->weekly_bookings();
        $this->pageData['total_bookings'] = $bookingModel->total_bookings();

        return view('admin/general/dashboard', [
            'page_title' => 'Dashboard',
            'page_data' => $this->pageData,
            'user' => $this->adminUser,
        ]);
    }

    public function settings()
    {
        $data = [
            'page_title' => 'General Settings',
            'page_data' => $this->pageData,
            'user' => $this->adminUser,
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $rules = [
                'site-title' => 'required',
                'site-description' => 'required',
                'site-keywords' => 'required',
            ];

            if ($this->validate($rules)) {
                $toUpdate = [
                    'title' => $this->request->getPost('site-title'),
                    'description' => $this->request->getPost('site-description'),
                    'keywords' => $this->request->getPost('site-keywords'),
                ];

                $this->handleSettingsUpload('site-logo', 'logo', $toUpdate, $data);
                $this->handleSettingsUpload('site-favicon', 'favicon', $toUpdate, $data);

                $generalModel = new GeneralModel();
                $generalModel->updateSettings($toUpdate);
                $data['page_data']['general'] = $generalModel->get();
                $data['alert'] = [
                    'type' => 'alert alert-success',
                    'msg' => 'General settings updated successfully.',
                ];
            }
        }

        return view('admin/general/settings', $data);
    }

    public function themes()
    {
        $themesModel = new ThemesModel();

        return view('admin/general/themes/main', [
            'page_title' => 'Theme Settings',
            'page_data' => $this->pageData,
            'user' => $this->adminUser,
            'current_theme' => $themesModel->get(),
            'themes' => $themesModel->getAvailableThemes(),
        ]);
    }

    public function upload_theme()
    {
        $data = [
            'page_title' => 'Theme Upload',
            'page_data' => $this->pageData,
            'user' => $this->adminUser,
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $file = $this->request->getFile('theme');

            if ($file && $file->isValid() && strtolower($file->getExtension()) === 'zip') {
                $filename = $file->getRandomName();
                $uploadDir = WRITEPATH . 'uploads/themes';
                $file->move($uploadDir, $filename);
                $zipPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                $themeName = strtolower(pathinfo($filename, PATHINFO_FILENAME));

                $zip = new ZipArchive();
                if ($zip->open($zipPath) === true) {
                    $zip->extractTo(APPPATH . 'Views/themes/' . $themeName);
                    $zip->close();
                    @unlink($zipPath);

                    session()->setFlashdata('alert', [
                        'type' => 'alert alert-success',
                        'msg' => 'Theme Installed successfully. To use the new theme, Activate it from the list below.',
                    ]);

                    return redirect()->to(base_url(GENERAL_CONTROLLER . '/themes'));
                }

                @unlink($zipPath);
            }

            $data['alert'] = [
                'type' => 'alert alert-danger',
                'msg' => 'Unknown file format. Please only upload .zip files.',
            ];
        }

        return view('admin/general/themes/upload', $data);
    }

    public function set_theme($theme = null)
    {
        if ($theme && ! ($this->adminUser['disabled'] ?? false)) {
            $themesModel = new ThemesModel();
            $manifest = $themesModel->doesThemeExist($theme);

            if ($manifest) {
                $themesModel->updateSettings(['theme' => trim(strtolower((string) $theme))]);
                session()->setFlashdata('alert', [
                    'type' => 'alert alert-success',
                    'msg' => ($manifest['name'] ?? $theme) . ' was applied successfully.',
                ]);
            }
        }

        return redirect()->to(base_url(GENERAL_CONTROLLER . '/themes'));
    }

    public function purge_cache()
    {
        cache()->clean();
        session()->setFlashdata('alert', [
            'type' => 'alert alert-success',
            'msg' => 'Destroyed all cache successfully.',
        ]);

        return redirect()->to(base_url(GENERAL_CONTROLLER . '/dashboard'));
    }

    private function handleSettingsUpload(string $field, string $setting, array &$toUpdate, array &$data): void
    {
        $file = $this->request->getFile($field);

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return;
        }

        if (! $file->isValid() || ! in_array(strtolower($file->getExtension()), ['gif', 'jpg', 'jpeg', 'png', 'svg'], true)) {
            $data[$setting . '_error'] = 'Only .gif, .jpg, .jpeg, .png, .svg Files are allowed.';
            return;
        }

        $filename = $file->getRandomName();
        $file->move(FCPATH . 'uploads/img', $filename);
        $toUpdate[$setting] = $filename;
    }
}

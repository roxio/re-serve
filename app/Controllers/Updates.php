<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\MainModel;
use App\Models\UpdatesModel;

class Updates extends BaseController
{
    private array $pageData;
    private ?array $adminUser;
    private UpdatesModel $updatesModel;

    public function __construct()
    {
        $mainModel = new MainModel();
        $this->updatesModel = new UpdatesModel();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $this->updatesModel->updateInfo();
        $this->adminUser = (new AdminModel())->adminDetails();
    }

    public function index()
    {
        return redirect()->to(base_url(UPDATES_CONTROLLER . '/main'));
    }

    public function main(): string
    {
        return view('admin/updates/main', [
            'page_title' => 'Updates',
            'page_data' => $this->pageData,
            'user' => $this->adminUser,
            'load_scripts' => [],
            'alert' => [
                'type' => 'alert alert-info',
                'msg' => 'The legacy auto-updater is disabled in the CI4 migration. Apply CI4 updates through source control and database migrations.',
            ],
        ]);
    }

    public function ajax_extract_package()
    {
        return $this->legacyUpdaterDisabled();
    }

    public function ajax_import_database()
    {
        return $this->legacyUpdaterDisabled();
    }

    public function ajax_finalize_settings()
    {
        return $this->legacyUpdaterDisabled();
    }

    private function legacyUpdaterDisabled()
    {
        return $this->response->setJSON([
            'success' => 'false',
            'message' => 'Legacy CI3 updater actions are disabled after migration to CI4.',
        ]);
    }
}

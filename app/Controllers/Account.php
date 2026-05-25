<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\MainModel;

class Account extends BaseController
{
    private array $pageData;
    private ?array $adminUser;
    private AdminModel $adminModel;

    public function __construct()
    {
        $mainModel = new MainModel();
        $this->adminModel = new AdminModel();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->adminUser = $this->adminModel->adminDetails();
    }

    public function me()
    {
        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'My Account',
            'user' => $this->adminUser,
        ];

        if ($this->request->getPost('submit')) {
            $fullName = str_replace(' ', '', (string) $this->request->getPost('admin-fullName'));
            $email = (string) $this->request->getPost('admin-email');
            $newPassword = (string) $this->request->getPost('admin-new-password');
            $password = (string) $this->request->getPost('admin-password');
            $rules = ['admin-password' => 'required'];
            $toUpdate = [];

            if ($fullName !== (string) $data['user']['fullName']) {
                $rules['admin-fullName'] = 'required|is_unique[logintbl.fullName]';
                $toUpdate['fullName'] = strtolower($fullName);
            }

            if ($email !== (string) $data['user']['email']) {
                $rules['admin-email'] = 'required|valid_email|is_unique[logintbl.email]';
                $toUpdate['email'] = strtolower($email);
            }

            if ($newPassword !== '') {
                $rules['admin-new-password'] = 'min_length[3]|max_length[48]';
                $toUpdate['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            if ($this->validate($rules)) {
                if ($this->adminModel->verifyPassword($data['user']['id'], $password)) {
                    if ($toUpdate !== [] && ! $data['user']['disabled']) {
                        $this->adminModel->updateAccount($data['user']['id'], $toUpdate);
                    }

                    $data['user'] = $this->adminModel->recreateSession();
                    $data['alert'] = [
                        'type' => 'alert alert-success',
                        'msg' => 'Account details updated successfully.',
                    ];
                } else {
                    $data['alert'] = [
                        'type' => 'alert alert-danger',
                        'msg' => 'Invalid current Password provided.',
                    ];
                }
            }
        }

        return view('admin/account/me', $data);
    }
}

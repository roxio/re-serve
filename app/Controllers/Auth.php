<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\MainModel;

class Auth extends BaseController
{
    private array $pageData;
    private AdminModel $adminModel;

    public function __construct()
    {
        $mainModel = new MainModel();
        $this->adminModel = new AdminModel();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
    }

    public function index()
    {
        return redirect()->to(base_url(GENERAL_CONTROLLER));
    }

    public function login()
    {
        if ($this->adminModel->adminDetails()) {
            return redirect()->to(base_url(GENERAL_CONTROLLER . '/dashboard'));
        }

        $data = [
            'page_title' => 'Login',
            'body_class' => 'login',
            'page_data' => $this->pageData,
            'error' => false,
            'redirect_to' => $this->request->getGet('redirect'),
        ];

        if ($this->request->getPost('submit') !== null) {
            $rules = [
                'identifier' => 'required',
                'password' => 'required',
            ];

            if ($this->validate($rules)) {
                $user = $this->adminModel->login(
                    $this->request->getPost('identifier'),
                    $this->request->getPost('password'),
                    $this->request->getPost('remember-me')
                );

                if ($user) {
                    $redirectTo = $this->request->getPost('redirect');

                    if ($redirectTo) {
                        return redirect()->to(urldecode($redirectTo));
                    }

                    return redirect()->to(base_url(GENERAL_CONTROLLER));
                }

                $data['error'] = 'Invalid Credentials.';
            }
        }

        return view('admin/auth/login', $data);
    }

    public function logout()
    {
        $this->adminModel->logout();

        return redirect()->to(base_url(AUTH_CONTROLLER . '/login'));
    }
}

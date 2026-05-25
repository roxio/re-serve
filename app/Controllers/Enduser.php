<?php

namespace App\Controllers;

use App\Models\LoginModel;
use App\Models\MainModel;

class Enduser extends BaseController
{
    public function index()
    {
        $session = session();
        $loginModel = new LoginModel();
        $userId = $session->get('id');
        $user = $loginModel->user_info($userId);

        if (! $user) {
            return redirect()->to(base_url('login'));
        }

        $data = $this->themeData([
            'title' => 'User Settings',
            'user' => $user,
            'session' => $session,
        ]);

        if ($this->request->getMethod() === 'POST' && $this->request->getPost('submit-pass') !== null) {
            $data['tab'] = 'password';
            $this->handlePasswordUpdate($loginModel, $userId, $data);
        } elseif ($this->request->getMethod() === 'POST' && $this->request->getPost('submit-acc') !== null) {
            $data['tab'] = 'account';
            $this->handleAccountUpdate($loginModel, $user, $data);
        }

        $data['user'] = $loginModel->user_info($userId);

        return view('themes/' . ($data['theme'] ?? 'redishtheme') . '/enduser', $data);
    }

    private function handlePasswordUpdate(LoginModel $loginModel, $userId, array &$data): void
    {
        if (! $this->validate([
            'password' => 'required',
            'newpassword' => 'required|min_length[3]',
        ])) {
            return;
        }

        if ($loginModel->verify_password($userId, $this->request->getPost('password'))) {
            $loginModel->set_new_password($userId, $this->request->getPost('newpassword'));
            $data['alert'] = [
                'type' => 'alert alert-success',
                'msg' => 'Password has been changed.',
            ];

            return;
        }

        $data['alert'] = [
            'type' => 'alert alert-danger',
            'msg' => 'Old password is in-valid.',
        ];
    }

    private function handleAccountUpdate(LoginModel $loginModel, array $user, array &$data): void
    {
        $data['alert'] = [
            'type' => 'alert alert-success',
            'msg' => 'Updated Successfully.',
        ];

        $avatar = $this->request->getFile('avatar');

        if ($avatar && $avatar->isValid() && ! $avatar->hasMoved()) {
            if (! in_array($avatar->getMimeType(), ['image/png', 'image/svg+xml', 'image/svg', 'image/webp', 'image/jpeg', 'image/gif'], true)) {
                $data['alert'] = [
                    'type' => 'alert alert-danger',
                    'msg' => 'Image type not matched.',
                ];
            } else {
                $extension = $avatar->getExtension() ?: 'jpg';
                $fileName = md5((string) $user['id']) . '.' . $extension;
                $avatar->move(FCPATH . 'uploads/user', $fileName, true);
                $loginModel->set_new_avatar($fileName, $user['id']);
            }
        }

        $fullname = strtolower(str_replace(' ', '', (string) $this->request->getPost('fullname')));

        if ($fullname && $fullname !== $user['fullName']) {
            $exists = $loginModel
                ->where('fullName', $fullname)
                ->where('id !=', $user['id'])
                ->countAllResults() > 0;

            if ($exists || strlen($fullname) < 2 || strlen($fullname) > 20) {
                $data['alert'] = [
                    'type' => 'alert alert-danger',
                    'msg' => 'Username is not Update;',
                ];

                return;
            }

            $loginModel->set_new_fullname($fullname, $user['id']);
        }
    }

    private function themeData(array $extra = []): array
    {
        $mainModel = new MainModel();
        $pageData = $mainModel->pageData();

        return array_merge($pageData, $extra);
    }
}

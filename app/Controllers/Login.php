<?php

namespace App\Controllers;

use App\Models\LoginModel;
use App\Models\MainModel;

class Login extends BaseController
{
    public function index()
    {
        if (session()->get('id')) {
            return redirect()->to(base_url());
        }

        if ($this->request->getMethod() === 'POST') {
            if (! $this->validate([
                'email' => 'required',
                'password' => 'required',
            ])) {
                return redirect()->to(base_url('login'))->withInput();
            }

            $loginModel = new LoginModel();
            $login = $loginModel->isValidate(
                $this->request->getPost('email'),
                $this->request->getPost('password')
            );

            if ($login && $login['activation'] && $login['id']) {
                session()->set('id', $login['id']);

                return redirect()->to(base_url());
            }

            if ($login && ! $login['activation']) {
                $this->flash('You have not activate your account yet. Please check your Email.', 'alert alert-danger');
            } elseif ($login) {
                $this->flash('Username / Email / Password Not Matched', 'alert alert-danger');
            } else {
                $this->flash('Account not find by this email.', 'alert alert-danger');
            }

            return redirect()->to(base_url('login'));
        }

        return $this->renderTheme('login');
    }

    public function signUp()
    {
        if (session()->get('id')) {
            return redirect()->to(base_url());
        }

        if ($this->request->getMethod() === 'POST') {
            if (! $this->validate([
                'fullName' => 'required|min_length[2]|max_length[20]|is_unique[logintbl.fullName]',
                'email' => 'required|valid_email|is_unique[logintbl.email]',
                'phone' => 'required|regex_match[/([0-9\s\-]{7,})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/]',
                'password' => 'required',
                'userCnPassword' => 'required|matches[password]',
            ])) {
                return redirect()->to(base_url('login/signUp'))->withInput();
            }

            $loginModel = new LoginModel();
            $mainModel = new MainModel();
            $activationCode = md5(time() . random_int(1, 100000));
            $email = (string) $this->request->getPost('email');
            $password = (string) $this->request->getPost('password');

            $created = $loginModel->addUser([
                'fullName' => str_replace(' ', '', (string) $this->request->getPost('fullName')),
                'email' => $email,
                'phone' => $this->request->getPost('phone'),
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'activated' => '0',
                'activationCode' => $activationCode,
            ]);

            if ($created['return']) {
                $sent = $loginModel->sendActivation($email, '', $activationCode, $mainModel->smtp_settings() ?? []);
                $this->flash(
                    $sent
                        ? 'You have Sign Up Successfully please check your email for Verification.'
                        : 'Account created, but activation email was not sent because SMTP is not configured.',
                    $sent ? 'alert alert-success' : 'alert alert-warning'
                );
            } else {
                $this->flash('Something went wrong please signup again or check your Email.', 'alert alert-danger');
            }

            return redirect()->to(base_url('login/signUp'));
        }

        return $this->renderTheme('signup');
    }

    public function activate($code = null)
    {
        $loginModel = new LoginModel();

        if ($code && $loginModel->activate($code)) {
            $this->flash('Your account Activated Successfully.', 'alert alert-success');

            return redirect()->to(base_url('login'));
        }

        $this->flash('Sorry! your activation code is wrong, Please Contact with us if you have any problem.', 'alert alert-danger');

        return $this->renderTheme('activate');
    }

    public function reset()
    {
        if ($this->request->getMethod() === 'POST') {
            if (! $this->validate(['email' => 'required|valid_email'])) {
                return redirect()->to(base_url('login/reset'))->withInput();
            }

            $email = (string) $this->request->getPost('email');
            $loginModel = new LoginModel();

            if (! $loginModel->isValidEmail($email)) {
                $this->flash('Could not find your email.', 'alert alert-danger');

                return redirect()->to(base_url('login/reset'));
            }

            $randomPassword = bin2hex(random_bytes(6));
            $hash = password_hash($randomPassword, PASSWORD_DEFAULT);
            $loginModel->updatePassword($loginModel->get_id_by_email($email), $hash);
            $sent = $loginModel->sendNewPassword($email, $randomPassword, (new MainModel())->smtp_settings() ?? []);

            $this->flash(
                $sent
                    ? 'You have Successfully generated a news password, please check your email.'
                    : 'Password was changed, but email was not sent because SMTP is not configured.',
                $sent ? 'alert alert-success' : 'alert alert-warning'
            );

            return redirect()->to(base_url('login'));
        }

        return $this->renderTheme('reset');
    }

    public function logout()
    {
        session()->remove('id');

        return redirect()->to(base_url('login'));
    }

    private function renderTheme(string $view): string
    {
        $mainModel = new MainModel();
        $pageData = $mainModel->pageData();
        $theme = $pageData['theme'] ?? 'redishtheme';

        return view('themes/' . $theme . '/' . $view, array_merge($pageData, [
            'email' => $mainModel->smtp_settings(),
            'user' => (new LoginModel())->user_info(session()->get('id')),
            'social_keys' => $mainModel->social_keys(),
            'session' => session(),
        ]));
    }

    private function flash(string $message, string $class): void
    {
        session()->setFlashdata('userMsg', $message);
        session()->setFlashdata('userMsg_class', $class);
    }
}

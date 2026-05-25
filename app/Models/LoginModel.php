<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginModel extends Model
{
    protected $table = 'logintbl';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'fullName',
        'email',
        'password',
        'verifiedEmail',
        'image',
        'photoURL',
        'role',
        'phone',
        'gender',
        'bookingId',
        'activated',
        'activationCode',
        'google',
        'facebook',
        'privacy',
    ];

    public function addUser(array $data): array
    {
        $userId = $this->insert($data, true);

        return [
            'return' => (bool) $userId,
            'userId' => $userId ?: null,
        ];
    }

    public function addUserPhone($id, $phoneNumber): bool
    {
        return $this->update($id, ['phone' => $phoneNumber]);
    }

    public function socialLogin(string $email, string $photoURL, string $provider): array|false
    {
        $email = trim(strtolower($email));
        $provider = in_array($provider, ['google', 'facebook'], true) ? $provider : '';

        if ($email === '' || $provider === '') {
            return false;
        }

        $id = $this->get_id_by_email($email);

        if ($id) {
            $this->update($id, [
                $provider => 1,
                'activated' => 1,
                'verifiedEmail' => 1,
                'photoURL' => $photoURL,
            ]);

            return [
                'nlg' => false,
                'userId' => $id,
                'email' => $email,
            ];
        }

        $randomPassword = bin2hex(random_bytes(6));
        $userId = $this->insert([
            'fullName' => $this->uniqueSocialFullName($email),
            'email' => $email,
            'password' => password_hash($randomPassword, PASSWORD_DEFAULT),
            'verifiedEmail' => 1,
            'activated' => 1,
            'image' => '',
            'photoURL' => $photoURL,
            'role' => 0,
            'phone' => '',
            'gender' => '',
            'bookingId' => 0,
            'activationCode' => '',
            'google' => $provider === 'google' ? 1 : 0,
            'facebook' => $provider === 'facebook' ? 1 : 0,
            'privacy' => 0,
        ], true);

        if (! $userId) {
            return false;
        }

        return [
            'nlg' => true,
            'userId' => $userId,
            'email' => $email,
            'randomStr' => $randomPassword,
        ];
    }

    public function isValidate($identifier, $password)
    {
        $identifier = trim(strtolower((string) $identifier));
        $password = trim((string) $password);

        $user = $this->groupStart()
            ->where('fullName', $identifier)
            ->orWhere('email', $identifier)
            ->groupEnd()
            ->first();

        if (! $user) {
            return false;
        }

        if ((string) $user['activated'] !== '1' || (string) $user['verifiedEmail'] !== '1') {
            return ['activation' => false];
        }

        return [
            'activation' => true,
            'id' => password_verify($password, $user['password']) ? $user['id'] : false,
        ];
    }

    public function activate($code): bool
    {
        $user = $this->where('activationCode', $code)->first();

        if (! $user) {
            return false;
        }

        return $this->update($user['id'], [
            'activated' => '1',
            'verifiedEmail' => '1',
        ]);
    }

    public function user_info($id)
    {
        if (! $id) {
            return false;
        }

        return $this->find($id) ?: false;
    }

    public function get_id_by_email($email)
    {
        $user = $this->select('id')
            ->where('email', trim(strtolower((string) $email)))
            ->first();

        return $user['id'] ?? false;
    }

    public function isValidEmail($email): bool
    {
        return $this->where('email', trim(strtolower((string) $email)))->countAllResults() > 0;
    }

    public function updatePassword($id, $password): bool
    {
        return $this->update($id, ['password' => $password]);
    }

    public function verify_password($userId, $password): bool
    {
        $user = $this->select('password')->find($userId);

        return $user ? password_verify((string) $password, $user['password']) : false;
    }

    public function set_new_password($userId, $password): bool
    {
        return $this->update($userId, [
            'password' => password_hash(trim((string) $password), PASSWORD_DEFAULT),
        ]);
    }

    public function set_new_avatar($filename, $id): bool
    {
        return $this->update($id, ['image' => $filename]);
    }

    public function set_new_fullname($fullname, $id): bool
    {
        return $this->update($id, [
            'fullName' => trim(strtolower((string) $fullname)),
        ]);
    }

    public function total_registrations(): int
    {
        return $this->where('role', 0)->countAllResults();
    }

    public function recent_registrations($num)
    {
        $rows = $this->where('role', 0)
            ->orderBy('id', 'desc')
            ->limit((int) $num)
            ->findAll();

        return $rows !== [] ? $rows : false;
    }

    public function weekly_registrations(): int
    {
        return $this->where('YEARWEEK(`register_date`) = YEARWEEK(NOW())', null, false)
            ->countAllResults();
    }

    public function validateSession($loginSession)
    {
        $user = $this->select('id')->find($loginSession);

        return $user['id'] ?? false;
    }

    public function sendActivation(string $email, string $randomPassword, string $activationCode, array $smtpDetails): bool
    {
        $mail = service('email');
        $config = [
            'charset' => 'UTF-8',
            'wordWrap' => true,
            'mailType' => 'html',
            'newline' => "\r\n",
        ];

        if ((int) ($smtpDetails['status'] ?? 0) === 1) {
            $config = array_merge($config, [
                'protocol' => 'smtp',
                'SMTPHost' => $smtpDetails['host'] ?? '',
                'SMTPPort' => (int) ($smtpDetails['port'] ?? 0),
                'SMTPUser' => $smtpDetails['username'] ?? '',
                'SMTPPass' => $smtpDetails['password'] ?? '',
            ]);
        }

        $mail->initialize($config);

        $from = $smtpDetails['email'] ?? '';
        if ($from === '') {
            return false;
        }

        $mail->setFrom($from, 'Salon Script');
        $mail->setTo($email);
        $mail->setSubject('Salon Activation');
        $passwordNotice = $randomPassword !== ''
            ? ' After activate your account you can login with this PASSWORD: ' . esc($randomPassword)
            : '';

        $mail->setMessage(
            '<div style="padding:25px;border-radius:5px;background-color:#fff;max-width:500px;margin:30px auto;border: #343a40 1px solid;">'
            . '<h1 style="font-size: 40px;text-align: center;line-height: initial;font-weight: 700;margin: 0 0 0;">Activate your Account</h1>'
            . '<p style="font-size: 15px;line-height: 25px;margin: 10px 0 5px;text-align: center;color: #343a40;font-weight: 400;">'
            . 'Click on Activate Now Button for activation.'
            . $passwordNotice
            . '</p><a href="'
            . base_url('login/activate/' . $activationCode)
            . '" target="_blank" style="text-align:center;font-size:21px;line-height:40px;margin-top:20px;display:block;padding:.375rem 0;border-radius:.25rem;background-color: #343a40;border-color: #343a40;color: #fff;text-decoration: none;">Activate Now</a></div>'
        );

        return $mail->send(false);
    }

    public function sendNewPassword(string $email, string $randomPassword, array $smtpDetails): bool
    {
        $mail = service('email');
        $config = [
            'charset' => 'UTF-8',
            'wordWrap' => true,
            'mailType' => 'html',
            'newline' => "\r\n",
        ];

        if ((int) ($smtpDetails['status'] ?? 0) === 1) {
            $config = array_merge($config, [
                'protocol' => 'smtp',
                'SMTPHost' => $smtpDetails['host'] ?? '',
                'SMTPPort' => (int) ($smtpDetails['port'] ?? 0),
                'SMTPUser' => $smtpDetails['username'] ?? '',
                'SMTPPass' => $smtpDetails['password'] ?? '',
            ]);
        }

        $from = $smtpDetails['email'] ?? '';
        if ($from === '') {
            return false;
        }

        $mail->initialize($config);
        $mail->setFrom($from, 'Salon Script');
        $mail->setTo($email);
        $mail->setSubject('Beauty Salon New Password');
        $mail->setMessage(
            '<div style="padding:25px;border-radius:5px;background-color:#fff;max-width:500px;margin:30px auto;border: #343a40 1px solid;">'
            . '<h1 style="font-size: 40px;text-align: center;line-height: initial;font-weight: 700;margin: 0 0 0;">New Password</h1>'
            . '<p style="font-size: 15px;line-height: 25px;margin: 10px 0 5px;text-align: center;color: #343a40;font-weight: 400;">'
            . 'You have generated a new password for salon booking system, New Password: '
            . esc($randomPassword)
            . '</p></div>'
        );

        return $mail->send(false);
    }

    private function uniqueSocialFullName(string $email): string
    {
        $base = preg_replace('/[^a-z0-9]/', '', strtolower(strstr($email, '@', true) ?: 'user'));
        $base = $base !== '' ? substr($base, 0, 18) : 'user';
        $candidate = $base;
        $index = 1;

        while ($this->where('fullName', $candidate)->countAllResults() > 0) {
            $suffix = (string) $index;
            $candidate = substr($base, 0, 20 - strlen($suffix)) . $suffix;
            $index++;
        }

        return $candidate;
    }
}

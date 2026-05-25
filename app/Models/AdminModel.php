<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table = 'logintbl';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'fullName',
        'email',
        'password',
        'role',
    ];

    public function login($identifier, $password, $remember = null)
    {
        $identifier = trim(strtolower((string) $identifier));
        $password = trim((string) $password);

        $user = $this->groupStart()
            ->where('fullName', $identifier)
            ->orWhere('email', $identifier)
            ->groupEnd()
            ->first();

        if (! $user || (string) $user['role'] !== '1' || ! password_verify($password, $user['password'])) {
            return false;
        }

        session()->set([
            'admin_id' => $user['id'],
            'admin_fullName' => $user['fullName'],
            'admin_email' => $user['email'],
            'admin_role' => $user['role'],
        ]);

        return $user;
    }

    public function verifyPassword($id, $password): bool
    {
        $user = $this->select('password')->find($id);

        return $user ? password_verify((string) $password, $user['password']) : false;
    }

    public function updateAccount($id, array $fields): bool
    {
        return $this->update($id, $fields);
    }

    public function recreateSession()
    {
        $id = session()->get('admin_id');

        if (! $id) {
            return false;
        }

        $user = $this->find($id);

        if (! $user) {
            $this->logout();
            return false;
        }

        session()->set([
            'admin_id' => $user['id'],
            'admin_fullName' => $user['fullName'],
            'admin_email' => $user['email'],
            'admin_role' => $user['role'],
        ]);

        $user['disabled'] = DEMO_MODE;

        return $user;
    }

    public function adminDetails(): ?array
    {
        $session = session();

        if (! $session->get('admin_id')) {
            return null;
        }

        return [
            'id' => $session->get('admin_id'),
            'fullName' => $session->get('admin_fullName'),
            'email' => $session->get('admin_email'),
            'role' => $session->get('admin_role'),
            'disabled' => DEMO_MODE,
        ];
    }

    public function logout(): bool
    {
        session()->remove([
            'admin_id',
            'admin_fullName',
            'admin_email',
            'admin_role',
        ]);

        return true;
    }
}

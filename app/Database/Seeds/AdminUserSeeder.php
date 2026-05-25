<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = [
            'fullName' => 'admin1',
            'email' => 'admin@gmail.com',
            'password' => password_hash('test', PASSWORD_DEFAULT),
            'verifiedEmail' => 1,
            'image' => '',
            'photoURL' => '',
            'role' => 1,
            'phone' => '',
            'gender' => '',
            'bookingId' => 0,
            'activated' => 1,
            'activationCode' => '',
            'google' => 0,
            'facebook' => 0,
            'privacy' => 0,
        ];

        $existing = $this->db
            ->table('logintbl')
            ->select('id')
            ->where('fullName', 'admin1')
            ->orWhere('email', 'admin@gmail.com')
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('logintbl')
                ->where('id', $existing['id'])
                ->update($admin);

            return;
        }

        $this->db->table('logintbl')->insert($admin);
    }
}

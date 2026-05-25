<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'orderId',
        'userId',
        'serviceId',
        'bookingId',
        'transectionId',
        'paid_amount',
        'paid_amount_cents',
        'paid_currency',
        'receipt_url',
        'customer_email',
        'payment_status',
        'created',
    ];

    public function insertOrder(array $data)
    {
        return $this->insert($data, true) ?: false;
    }

    public function getStripe(): array
    {
        $row = $this->db
            ->table('stripe-settings')
            ->where('id', 1)
            ->limit(1)
            ->get()
            ->getRowArray();

        return array_merge($this->defaultStripe(), is_array($row) ? $row : []);
    }

    public function getOrder($orderId): ?array
    {
        return $this->where('orderId', $orderId)->first();
    }

    public function getAllOrders(): array
    {
        return $this->findAll();
    }

    public function orderByAllData()
    {
        $rows = $this->db
            ->table('orders')
            ->select('orders.*, orders.id as id, servicetable.id as service_id, logintbl.id as logintbl_id, servicetable.image as servicetable_image, servicetable.title, logintbl.email')
            ->join('servicetable', 'orders.serviceId = servicetable.id')
            ->join('logintbl', 'orders.userId = logintbl.id')
            ->orderBy('orders.id', 'desc')
            ->get()
            ->getResultArray();

        return $rows !== [] ? $rows : false;
    }

    public function setStripe(array $fields): bool
    {
        return $this->db->table('stripe-settings')->where('id', 1)->update($fields);
    }

    private function defaultStripe(): array
    {
        return [
            'stripe_api_key' => '',
            'stripe_publishable_key' => '',
            'stripe_currency' => '',
            'status' => 0,
        ];
    }
}

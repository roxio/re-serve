<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table = 'bookingtbl';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'serviceId',
        'serviceBill',
        'adults',
        'childrens',
        'date',
        'timing',
        'agentId',
        'userId',
        'paymentStatus',
        'serviceStatus',
        'orderId',
    ];

    public function agentExist($service, $date, $time)
    {
        $rows = $this->select('agentId')
            ->where([
                'serviceId' => $service,
                'date' => $date,
                'timing' => $time,
            ])
            ->findAll();

        return $rows !== [] ? $rows : false;
    }

    public function addBooking(array $data): array
    {
        $bookingId = $this->insert($data, true);

        return [
            'return' => (bool) $bookingId,
            'bokingId' => $bookingId ?: null,
        ];
    }

    public function setBooking($id, array $fields): bool
    {
        return $this->update($id, $fields);
    }

    public function getBooking($id): ?array
    {
        return $this->find($id);
    }

    public function getBookingbyOrderId($orderId): ?array
    {
        return $this->where('orderId', $orderId)->first();
    }

    public function getbyid($id)
    {
        $rows = $this->db
            ->table('bookingtbl')
            ->select('bookingtbl.*, bookingtbl.id as id, servicetable.id as service_id, servicetable.image as servicetable_image, servicetable.title, servicetable.price, agents.id as agents_id, agents.agentName, orders.id as orders_id, orders.orderId as orders_orderId, orders.receipt_url')
            ->join('servicetable', 'bookingtbl.serviceId = servicetable.id')
            ->join('orders', 'bookingtbl.orderId = orders.orderId', 'left')
            ->join('agents', 'bookingtbl.agentId = agents.id', 'left')
            ->where('bookingtbl.userId', $id)
            ->where('servicetable.id !=', null)
            ->where('bookingtbl.serviceId !=', null)
            ->orderBy('bookingtbl.id', 'desc')
            ->get()
            ->getResultArray();

        return $rows !== [] ? $rows : false;
    }

    public function doesBookingExist(array $booking): bool
    {
        if (empty($booking['serviceId'])) {
            return false;
        }

        return $this
            ->where('serviceId', $booking['serviceId'])
            ->where('date', $booking['date'] ?? '')
            ->where('timing', $booking['timing'] ?? '')
            ->where('agentId', $booking['agentId'] ?? '')
            ->countAllResults() > 0;
    }

    public function recent_bookings($num)
    {
        $rows = $this->db
            ->table('bookingtbl')
            ->select('bookingtbl.*, bookingtbl.id as id, servicetable.id as service_id, logintbl.id as logintbl_id, agents.id as agents_id, servicetable.title, servicetable.price, agents.agentName, logintbl.fullName')
            ->join('servicetable', 'bookingtbl.serviceId = servicetable.id')
            ->join('logintbl', 'bookingtbl.userId = logintbl.id')
            ->join('agents', 'bookingtbl.agentId = agents.id', 'left')
            ->orderBy('bookingtbl.id', 'desc')
            ->limit((int) $num)
            ->get()
            ->getResultArray();

        return $rows !== [] ? $rows : false;
    }

    public function showAdminBookings()
    {
        $rows = $this->db
            ->table('bookingtbl')
            ->select('bookingtbl.*, bookingtbl.id as id, servicetable.id as service_id, logintbl.id as logintbl_id, agents.id as agents_id, servicetable.title, servicetable.price, agents.agentName, logintbl.fullName')
            ->join('servicetable', 'bookingtbl.serviceId = servicetable.id')
            ->join('logintbl', 'bookingtbl.userId = logintbl.id')
            ->join('agents', 'bookingtbl.agentId = agents.id', 'left')
            ->orderBy('bookingtbl.id', 'desc')
            ->get()
            ->getResultArray();

        return $rows !== [] ? $rows : false;
    }

    public function deleteBooking($id): bool
    {
        return $this->delete($id);
    }

    public function bookingConfirm($id, array $fields): bool
    {
        return $this->update($id, $fields);
    }

    public function bookingCancel($id, array $fields): bool
    {
        return $this->update($id, $fields);
    }

    public function bookingPay($id, array $fields): bool
    {
        return $this->update($id, $fields);
    }

    public function weekly_bookings(): int
    {
        return $this->where('YEARWEEK(`upload_date`) = YEARWEEK(NOW())', null, false)
            ->countAllResults();
    }

    public function total_bookings($user = null): int
    {
        if ($user) {
            $this->where('userId', $user);
        }

        return $this->countAllResults();
    }
}

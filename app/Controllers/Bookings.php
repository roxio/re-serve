<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\BookingModel;
use App\Models\MainModel;

class Bookings extends BaseController
{
    private array $pageData;
    private ?array $adminUser;
    private BookingModel $bookingModel;

    public function __construct()
    {
        $mainModel = new MainModel();
        $adminModel = new AdminModel();

        $this->bookingModel = new BookingModel();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->adminUser = $adminModel->adminDetails();
    }

    public function index()
    {
        return view('admin/bookings/bookings', [
            'page_data' => $this->pageData,
            'page_title' => 'All Bookings',
            'user' => $this->adminUser,
            'bookings' => $this->bookingModel->showAdminBookings(),
        ]);
    }

    public function deleteBookings($id = null, $confirm = false)
    {
        if ($this->canMutate($confirm)) {
            $this->bookingModel->deleteBooking($id);
            session()->setFlashdata('alert', [
                'type' => 'alert alert-success',
                'msg' => 'Successfully delete booking.',
            ]);
        }

        return redirect()->to(base_url(BOOKINGS_CONTROLLER));
    }

    public function bookingConfirm($id = null, $confirm = false)
    {
        if ($this->canMutate($confirm)) {
            $this->bookingModel->bookingConfirm($id, ['serviceStatus' => '1']);
            session()->setFlashdata('alert', [
                'type' => 'alert alert-success',
                'msg' => 'Successfully confirm booking.',
            ]);
        }

        return redirect()->to(base_url(BOOKINGS_CONTROLLER));
    }

    public function bookingCancel($id = null, $confirm = false)
    {
        if ($this->canMutate($confirm)) {
            $this->bookingModel->bookingCancel($id, ['serviceStatus' => '2']);
            session()->setFlashdata('alert', [
                'type' => 'alert alert-success',
                'msg' => 'Successfully cancel booking.',
            ]);
        }

        return redirect()->to(base_url(BOOKINGS_CONTROLLER));
    }

    public function bookingPay($id = null, $confirm = false)
    {
        if ($this->canMutate($confirm)) {
            $this->bookingModel->bookingPay($id, ['paymentStatus' => '1']);
            session()->setFlashdata('alert', [
                'type' => 'alert alert-success',
                'msg' => 'Successfully paid booking.',
            ]);
        }

        return redirect()->to(base_url(BOOKINGS_CONTROLLER));
    }

    private function canMutate($confirm): bool
    {
        return (bool) $confirm
            && ! ($this->adminUser['disabled'] ?? false)
            && (string) ($this->adminUser['role'] ?? '') === '1';
    }
}

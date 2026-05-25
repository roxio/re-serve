<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\LoginModel;
use App\Models\MainModel;
use App\Models\OrderModel;
use App\Models\ServiceModel;
use App\Libraries\StripePayment;

class Userbooking extends BaseController
{
    public function index()
    {
        $session = session();
        $userId = $session->get('id');
        $loginModel = new LoginModel();
        $user = $loginModel->user_info($userId);

        if (! $user) {
            return redirect()->to(base_url('login'));
        }

        $mainModel = new MainModel();
        $bookingModel = new BookingModel();
        $pageData = $mainModel->pageData();

        $data = array_merge($pageData, [
            'title' => 'Your Bookings',
            'user' => $user,
            'bookings' => $bookingModel->getbyid($userId),
            'stripe' => (new OrderModel())->getStripe(),
            'session' => $session,
        ]);

        return view('themes/' . ($data['theme'] ?? 'redishtheme') . '/userbooking', $data);
    }

    public function paynow($bookingId = '')
    {
        $session = session();
        $userId = $session->get('id');
        $loginModel = new LoginModel();
        $user = $loginModel->user_info($userId);

        if (! $user) {
            return redirect()->to(base_url('login'));
        }

        if (! $bookingId) {
            return redirect()->to(base_url('userbooking'));
        }

        $mainModel = new MainModel();
        $bookingModel = new BookingModel();
        $serviceModel = new ServiceModel();
        $orderModel = new OrderModel();
        $pageData = $mainModel->pageData();
        $booking = $bookingModel->getBooking($bookingId);

        if (! $booking || (string) $booking['userId'] !== (string) $userId) {
            return redirect()->to(base_url('userbooking'));
        }

        $service = $serviceModel->servicedataById($booking['serviceId']);
        $stripe = $orderModel->getStripe();

        if ((string) $this->request->getPost('selectPayment') === '1') {
            $token = (string) $this->request->getPost('stripeToken');

            if ($token === '' || ! $service) {
                return $this->response->setJSON([
                    'serviceAdded' => false,
                    'payment' => ['success' => false, 'msg' => 'Transaction failed!'],
                ]);
            }

            $payment = $this->processStripePayment($stripe, $user, $booking, $service);

            return $this->response->setJSON($payment);
        }

        $data = array_merge($pageData, [
            'title' => 'Pay By Stripe',
            'user' => $user,
            'booking' => $booking,
            'service' => $service,
            'stripe' => $stripe,
            'stripe_publishable_key' => $stripe['stripe_publishable_key'] ?? '',
            'session' => $session,
        ]);

        return view('themes/' . ($data['theme'] ?? 'redishtheme') . '/paynow', $data);
    }

    private function processStripePayment(array $stripe, array $user, array $booking, array $service): array
    {
        $token = (string) $this->request->getPost('stripeToken');
        $orderId = strtoupper(md5(str_replace('.', '', uniqid('', true) . time())));
        $totalPrice = ((int) $booking['adults'] + (int) $booking['childrens']) * (float) $service['price'];
        $stripePayment = new StripePayment($stripe);
        $customer = $stripePayment->addCustomer((string) $user['email'], $token);

        if (! $customer) {
            return [
                'serviceAdded' => false,
                'payment' => ['success' => false, 'msg' => 'Transaction failed!'],
            ];
        }

        $charge = $stripePayment->createCharge((string) $customer->id, (string) $service['title'], $totalPrice, $orderId);

        if (! $this->isSuccessfulCharge($charge)) {
            return [
                'serviceAdded' => false,
                'payment' => ['success' => false, 'msg' => 'Transaction failed!'],
            ];
        }

        $chargeId = $charge['id'];
        $bookingModel = new BookingModel();
        $orderModel = new OrderModel();
        $bookingSaved = $bookingModel->setBooking($booking['id'], [
            'orderId' => $chargeId,
            'paymentStatus' => '1',
            'serviceStatus' => '1',
        ]);

        $orderModel->insertOrder([
            'orderId' => $chargeId,
            'serviceId' => $service['id'],
            'bookingId' => $booking['id'],
            'transectionId' => $charge['balance_transaction'],
            'userId' => $user['id'],
            'paid_amount' => ((float) $charge['amount']) / 100,
            'paid_currency' => $charge['currency'],
            'receipt_url' => $charge['receipt_url'],
            'payment_status' => $charge['status'],
        ]);

        session()->setFlashdata('invMsg', 'Your Payment & Booking has bees Submited.');
        session()->setFlashdata('inv_class', 'alert alert-success');

        return [
            'serviceAdded' => $bookingSaved,
            'payment' => [
                'success' => true,
                'msg' => 'Transaction successful!',
                'orderid' => $chargeId,
            ],
        ];
    }

    private function isSuccessfulCharge($charge): bool
    {
        return is_array($charge)
            && (int) ($charge['amount_refunded'] ?? 1) === 0
            && empty($charge['failure_code'])
            && (int) ($charge['paid'] ?? 0) === 1
            && (int) ($charge['captured'] ?? 0) === 1;
    }
}

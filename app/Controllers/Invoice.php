<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\LoginModel;
use App\Models\MainModel;
use App\Models\OrderModel;
use App\Models\ServiceModel;

class Invoice extends BaseController
{
    public function index($orderId = null)
    {
        if (! $orderId) {
            return redirect()->to(base_url());
        }

        $orderModel = new OrderModel();
        $order = $orderModel->getOrder($orderId);

        if (! $order) {
            return redirect()->to(base_url('404'));
        }

        $mainModel = new MainModel();
        $pageData = $mainModel->pageData();

        return view('themes/' . ($pageData['theme'] ?? 'redishtheme') . '/invoice', array_merge($pageData, [
            'order' => $order,
            'user' => (new LoginModel())->user_info($order['userId']),
            'service' => (new ServiceModel())->servicedataById($order['serviceId']),
            'booking' => (new BookingModel())->getBookingbyOrderId($orderId),
            'session' => session(),
        ]));
    }
}

<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\MainModel;
use App\Models\OrderModel;

class Allpayments extends BaseController
{
    private array $pageData;
    private ?array $adminUser;
    private OrderModel $orderModel;

    public function __construct()
    {
        $mainModel = new MainModel();
        $adminModel = new AdminModel();

        $this->orderModel = new OrderModel();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->pageData['orders'] = $this->orderModel->orderByAllData();
        $this->adminUser = $adminModel->adminDetails();
    }

    public function index()
    {
        return view('admin/orders/orders', [
            'page_data' => $this->pageData,
            'page_title' => 'All Payments',
            'user' => $this->adminUser,
            'orders' => $this->pageData['orders'],
        ]);
    }

    public function stripe()
    {
        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Stripe Settings',
            'user' => $this->adminUser,
            'stripe' => $this->orderModel->getStripe(),
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $status = $this->request->getPost('site-status') ? 1 : 0;
            $rules = [];

            if ($status) {
                $rules = [
                    'stripe_api_key' => 'required',
                    'stripe_publishable_key' => 'required',
                    'stripe_currency' => 'required',
                ];
            }

            if ($rules === [] || $this->validate($rules)) {
                $this->orderModel->setStripe([
                    'status' => $status,
                    'stripe_api_key' => $this->request->getPost('stripe_api_key') ?: '',
                    'stripe_publishable_key' => $this->request->getPost('stripe_publishable_key') ?: '',
                    'stripe_currency' => $this->request->getPost('stripe_currency') ?: '',
                ]);

                $data['stripe'] = $this->orderModel->getStripe();
                $data['alert'] = [
                    'type' => 'alert alert-success',
                    'msg' => 'Stripe Settings updated successfully.',
                ];
            }
        }

        return view('admin/orders/stripe_settings', $data);
    }
}

<?php

namespace App\Controllers;

use App\Models\ContactDetails;
use App\Models\BookingModel;
use App\Models\GalleryModel;
use App\Models\LoginModel;
use App\Models\MainModel;
use App\Models\OrderModel;
use App\Models\ServiceModel;
use App\Libraries\StripePayment;

class Homepage extends BaseController
{
    public function index(): string
    {
        $mainModel = new MainModel();
        $pageData = $mainModel->pageData();
        $theme = $pageData['theme'] ?? 'redishtheme';

        $serviceModel = new ServiceModel();
        $galleryModel = new GalleryModel();
        $contactModel = new ContactDetails();
        $orderModel = new OrderModel();

        $viewData = array_merge($pageData, [
            'serviceList' => $serviceModel->serviceList(),
            'gcategories' => $galleryModel->listCat(),
            'galleryImages' => $galleryModel->listGallery(),
            'contactdetails' => $contactModel->getDetails() ?? [],
            'userinfo' => null,
            'stripe' => $orderModel->getStripe(),
            'session' => session(),
        ]);

        return view('themes/' . $theme . '/default', $viewData);
    }

    public function notfound(): string
    {
        $mainModel = new MainModel();
        $pageData = $mainModel->pageData();
        $theme = $pageData['theme'] ?? 'redishtheme';

        return view('themes/' . $theme . '/404', array_merge($pageData, [
            'session' => session(),
        ]));
    }

    public function selectagent()
    {
        $service = $this->request->getPost('service');
        $date = $this->request->getPost('date');
        $time = $this->request->getPost('time');

        $serviceModel = new ServiceModel();
        $bookingModel = new BookingModel();

        $serviceData = $serviceModel->servicedataById($service);

        return $this->response->setJSON([
            'service' => $serviceData,
            'agents' => $serviceData ? $serviceModel->selectAgents($serviceData['agentIds'] ?? '') : [],
            'exist' => $bookingModel->agentExist($service, $date, $time),
        ]);
    }

    public function selectFromDataById()
    {
        $bookingId = $this->request->getPost('bookingId');
        $serviceModel = new ServiceModel();
        $formData = $serviceModel->servicedataById($bookingId);

        if (! $formData) {
            return $this->response->setJSON(['success' => false]);
        }

        $formData['agents'] = $serviceModel->selectAgents($formData['agentIds'] ?? '');
        $formData['timing'] = $this->buildTimings(
            (string) ($formData['servStart'] ?? ''),
            (string) ($formData['servEnd'] ?? ''),
            (string) ($formData['servDuration'] ?? '')
        );

        return $this->response->setJSON($formData);
    }

    public function mailme()
    {
        $rules = [
            'name' => 'required|alpha_numeric|min_length[2]|max_length[20]',
            'email' => 'required|valid_email',
            'message' => 'required',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'name' => $this->validationError('name'),
                'email' => $this->validationError('email'),
                'message' => $this->validationError('message'),
            ]);
        }

        $mainModel = new MainModel();
        $pageData = $mainModel->pageData();
        $smtpDetails = $mainModel->smtp_settings() ?? [];

        if (! empty($pageData['recaptcha']['status']) && ! $this->verifyRecaptcha($pageData['recaptcha'])) {
            return $this->response->setJSON(['emailSent' => false]);
        }

        $email = service('email');
        $emailConfig = [
            'charset' => 'UTF-8',
            'wordWrap' => true,
            'mailType' => 'html',
            'newline' => "\r\n",
        ];

        if ((int) ($smtpDetails['status'] ?? 0) === 1) {
            $emailConfig = array_merge($emailConfig, [
                'protocol' => 'smtp',
                'SMTPHost' => $smtpDetails['host'] ?? '',
                'SMTPPort' => (int) ($smtpDetails['port'] ?? 0),
                'SMTPUser' => $smtpDetails['username'] ?? '',
                'SMTPPass' => $smtpDetails['password'] ?? '',
            ]);
        }

        $email->initialize($emailConfig);

        $recipient = $smtpDetails['email'] ?? ($pageData['general']['email'] ?? '');
        $senderEmail = (string) $this->request->getPost('email');
        $senderName = (string) $this->request->getPost('name');
        $message = (string) $this->request->getPost('message');
        $theme = $pageData['theme'] ?? 'redishtheme';
        $templatePath = APPPATH . 'Views/themes/' . $theme . '/email_templates/contact_message.html';
        $template = is_file($templatePath) ? file_get_contents($templatePath) : '{{content}}';

        $email->setFrom($recipient, 'Salon Script');
        $email->setReplyTo($senderEmail, $senderName);
        $email->setTo($recipient);
        $email->setSubject('New message from ' . esc($senderName));
        $email->setMessage(compile_template([
            'logo' => upload_url('img/' . ($pageData['general']['logo'] ?? '')),
            'web_url' => base_url(),
            'sender_name' => esc($senderName),
            'sender_email' => esc($senderEmail),
            'content' => nl2br(esc($message)),
            'year' => date('Y'),
            'name' => $pageData['general']['title'] ?? PRODUCT_NAME,
        ], $template));

        return $this->response->setJSON([
            'emailSent' => $recipient !== '' && $email->send(false),
        ]);
    }

    public function submitData()
    {
        $session = session();
        $loginSession = $session->get('id');
        $loginModel = new LoginModel();
        $bookingModel = new BookingModel();
        $serviceModel = new ServiceModel();
        $mainModel = new MainModel();

        $formService = [
            'serviceId' => $this->request->getPost('serviceTitle'),
            'adults' => $this->request->getPost('serviceAdult'),
            'childrens' => $this->request->getPost('serviceChildren'),
            'date' => $this->request->getPost('serviceDate'),
            'timing' => $this->request->getPost('serviceTiming'),
            'agentId' => $this->request->getPost('agent'),
        ];

        if (! $loginSession) {
            $rules = [
                'userFullName' => 'required|alpha_numeric|min_length[2]|max_length[20]|is_unique[logintbl.fullName]',
                'userEmail' => 'required|valid_email|is_unique[logintbl.email]',
                'userPhone' => 'required|regex_match[/([0-9\s\-]{7,})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/]',
            ];

            if (! $this->validate($rules)) {
                return $this->response->setJSON([
                    'userFullName' => $this->validationError('userFullName'),
                    'userEmail' => $this->validationError('userEmail'),
                    'userPhone' => $this->validationError('userPhone'),
                ]);
            }

            if ($bookingModel->doesBookingExist($formService)) {
                return $this->bookingResponse(false);
            }

            $randomPassword = bin2hex(random_bytes(6));
            $activationCode = md5(time() . random_int(1, 100000));
            $userInfo = [
                'fullName' => $this->request->getPost('userFullName'),
                'email' => $this->request->getPost('userEmail'),
                'phone' => $this->request->getPost('userPhone'),
                'password' => password_hash($randomPassword, PASSWORD_DEFAULT),
                'activated' => '0',
                'activationCode' => $activationCode,
            ];

            $loginId = $loginModel->addUser($userInfo);

            if (! $loginId['return']) {
                return $this->bookingResponse(false);
            }

            $mainData = $mainModel->pageData();
            $emailSent = $loginModel->sendActivation(
                (string) $userInfo['email'],
                $randomPassword,
                $activationCode,
                $mainModel->smtp_settings() ?? []
            );

            if (! $emailSent) {
                $session->setFlashdata('added', 'Email not sent, but the booking request was processed.');
                $session->setFlashdata('added_class', 'alert alert-warning');
            }

            $formService['userId'] = $loginId['userId'];
        } else {
            $user = $loginModel->user_info($loginSession);

            if (! $user) {
                return $this->bookingResponse(false);
            }

            if (empty($user['phone'])) {
                if (! $this->validate(['userPhone' => 'required'])) {
                    return $this->response->setJSON([
                        'userPhone' => $this->validationError('userPhone'),
                    ]);
                }

                $loginModel->addUserPhone($loginSession, $this->request->getPost('userPhone'));
            }

            if ($bookingModel->doesBookingExist($formService)) {
                return $this->bookingResponse(false);
            }

            $formService['userId'] = $loginSession;
        }

        $serviceData = $serviceModel->servicedataById($formService['serviceId']);
        $formService['serviceBill'] = $this->calculateServiceBill($serviceData, $formService);

        if ((string) $this->request->getPost('selectPayment') === '1') {
            return $this->processStripeBooking($formService, $serviceData);
        }

        $formService['paymentStatus'] = false;

        $booking = $bookingModel->addBooking($formService);

        if ($booking['return']) {
            $session->setFlashdata('added', 'Booking has been submitted. Please check your email for password and account activation.');
            $session->setFlashdata('added_class', 'alert alert-success');
        } else {
            $session->setFlashdata('added', 'Something went wrong, please try again.');
            $session->setFlashdata('added_class', 'alert alert-danger');
        }

        return $this->bookingResponse((bool) $booking['return']);
    }

    private function buildTimings(string $startTime, string $endTime, string $duration): array
    {
        $startingTime = strtotime($startTime);
        $endingTime = strtotime($endTime);
        $durationTime = strtotime($duration);

        if (! $startingTime || ! $endingTime || ! $durationTime) {
            return [];
        }

        $durationSeconds = ((int) date('G', $durationTime) * 60 + (int) date('i', $durationTime)) * 60;

        if ($durationSeconds <= 0) {
            return [];
        }

        $timings = [];

        while ($startingTime < $endingTime) {
            $endValue = $startingTime + $durationSeconds;
            $timings[] = date('h:i A', $startingTime) . ' - ' . date('h:i A', $endValue);
            $startingTime = $endValue;
        }

        return $timings;
    }

    private function validationError(string $field): string
    {
        $error = $this->validator?->getError($field);

        return $error ? '<small class="form-text text-danger">' . esc($error) . '</small>' : '';
    }

    private function bookingResponse(bool $success)
    {
        return $this->response->setJSON([
            'serviceAdded' => $success,
            'payment' => [
                'success' => $success,
                'msg' => $success ? 'You would be pay by cash!' : 'Something went wrong, please try again.',
                'orderid' => '',
            ],
        ]);
    }

    private function processStripeBooking(array $formService, ?array $serviceData)
    {
        $token = (string) $this->request->getPost('stripeToken');

        if ($token === '' || ! $serviceData) {
            return $this->response->setJSON([
                'serviceAdded' => false,
                'payment' => ['success' => false, 'msg' => 'Transaction failed!'],
            ]);
        }

        $loginModel = new LoginModel();
        $orderModel = new OrderModel();
        $bookingModel = new BookingModel();
        $user = $loginModel->user_info($formService['userId']);
        $stripe = $orderModel->getStripe();
        $orderId = strtoupper(md5(str_replace('.', '', uniqid('', true) . time())));
        $stripePayment = new StripePayment($stripe);
        $customer = $stripePayment->addCustomer((string) ($user['email'] ?? ''), $token);

        if (! $customer) {
            return $this->response->setJSON([
                'serviceAdded' => false,
                'payment' => ['success' => false, 'msg' => 'Transaction failed!'],
            ]);
        }

        $charge = $stripePayment->createCharge((string) $customer->id, (string) $serviceData['title'], (float) $formService['serviceBill'], $orderId);

        if (! $this->isSuccessfulCharge($charge)) {
            return $this->response->setJSON([
                'serviceAdded' => false,
                'payment' => ['success' => false, 'msg' => 'Transaction failed!'],
            ]);
        }

        $chargeId = $charge['id'];
        $formService['orderId'] = $chargeId;
        $formService['paymentStatus'] = true;
        $formService['serviceStatus'] = true;
        $booking = $bookingModel->addBooking($formService);

        if ($booking['return']) {
            $orderModel->insertOrder([
                'orderId' => $chargeId,
                'serviceId' => $formService['serviceId'],
                'bookingId' => $booking['bokingId'],
                'transectionId' => $charge['balance_transaction'],
                'userId' => $formService['userId'],
                'paid_amount' => ((float) $charge['amount']) / 100,
                'paid_currency' => $charge['currency'],
                'receipt_url' => $charge['receipt_url'],
                'payment_status' => $charge['status'],
            ]);

            session()->setFlashdata('invMsg', 'Your Payment & Booking has bees Submited.');
            session()->setFlashdata('inv_class', 'alert alert-success');
        }

        return $this->response->setJSON([
            'serviceAdded' => (bool) $booking['return'],
            'payment' => [
                'success' => (bool) $booking['return'],
                'msg' => $booking['return'] ? 'Transaction successful!' : 'Transaction failed!',
                'orderid' => $booking['return'] ? $chargeId : '',
            ],
        ]);
    }

    private function isSuccessfulCharge($charge): bool
    {
        return is_array($charge)
            && (int) ($charge['amount_refunded'] ?? 1) === 0
            && empty($charge['failure_code'])
            && (int) ($charge['paid'] ?? 0) === 1
            && (int) ($charge['captured'] ?? 0) === 1;
    }

    private function calculateServiceBill(?array $serviceData, array $booking): float
    {
        $price = (float) ($serviceData['price'] ?? 0);
        $adults = (int) ($booking['adults'] ?? 0);
        $children = (int) ($booking['childrens'] ?? 0);

        return ($adults + $children) * $price;
    }

    private function verifyRecaptcha(array $settings): bool
    {
        $response = $this->request->getPost('g-recaptcha-response');
        $secret = $settings['secret_key'] ?? '';

        if (! $response || ! $secret) {
            return false;
        }

        $client = service('curlrequest');
        $result = $client->post('https://www.google.com/recaptcha/api/siteverify', [
            'form_params' => [
                'secret' => $secret,
                'response' => $response,
                'remoteip' => $this->request->getIPAddress(),
            ],
            'http_errors' => false,
        ]);

        $decoded = json_decode($result->getBody(), true);

        return (bool) ($decoded['success'] ?? false);
    }
}

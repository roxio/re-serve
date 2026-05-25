<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\ContactDetails;
use App\Models\MainModel;

class Contact extends BaseController
{
    private array $pageData;
    private ?array $adminUser;
    private ContactDetails $contactDetails;

    public function __construct()
    {
        $mainModel = new MainModel();
        $adminModel = new AdminModel();

        $this->contactDetails = new ContactDetails();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->adminUser = $adminModel->adminDetails();
    }

    public function index()
    {
        return redirect()->to(base_url(CONTACT_CONTROLLER . '/contactDetails'));
    }

    public function contactDetails()
    {
        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Contact Settings',
            'user' => $this->adminUser,
            'contactDetails' => $this->contactDetails->get(),
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $rules = [
                'contact-phone' => 'required|regex_match[/([0-9\\s\\-]{7,})(?:\\s*(?:#|x\\.?|ext\\.?|extension)\\s*(\\d+))?$/]',
                'contact-email' => 'required|valid_email',
                'contact-address' => 'required',
                'map_src' => 'required',
                'map_wd' => 'required',
                'map_ht' => 'required',
                'contact-urlFb' => 'required',
                'contact-urlTwt' => 'required',
                'contact-urlIn' => 'required',
            ];

            if ($this->validate($rules)) {
                $this->contactDetails->updateDetails([
                    'phone' => htmlentities((string) $this->request->getPost('contact-phone')),
                    'email' => $this->request->getPost('contact-email'),
                    'address' => htmlentities((string) $this->request->getPost('contact-address')),
                    'map_src' => htmlentities((string) $this->request->getPost('map_src')),
                    'map_wd' => htmlentities((string) $this->request->getPost('map_wd')),
                    'map_ht' => htmlentities((string) $this->request->getPost('map_ht')),
                    'urlFb' => htmlentities((string) $this->request->getPost('contact-urlFb')),
                    'urlTwt' => htmlentities((string) $this->request->getPost('contact-urlTwt')),
                    'urlIn' => htmlentities((string) $this->request->getPost('contact-urlIn')),
                ]);

                $data['contactDetails'] = $this->contactDetails->get();
                session()->setFlashdata('alert', [
                    'type' => 'alert alert-success',
                    'msg' => 'Successfully updated contact details.',
                ]);
            }
        }

        return view('admin/contact/contact', $data);
    }
}

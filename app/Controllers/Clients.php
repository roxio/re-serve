<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\ClientsModel;
use App\Models\MainModel;

class Clients extends BaseController
{
    private array $pageData;
    private ?array $adminUser;
    private ClientsModel $clientsModel;

    public function __construct()
    {
        $mainModel = new MainModel();
        $adminModel = new AdminModel();

        $this->clientsModel = new ClientsModel();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->adminUser = $adminModel->adminDetails();
    }

    public function index()
    {
        return redirect()->to(base_url(CLIENTS_CONTROLLER . '/clients'));
    }

    public function clients()
    {
        return view('admin/clients/clients', [
            'page_data' => $this->pageData,
            'page_title' => 'All Clients',
            'user' => $this->adminUser,
            'clients' => $this->clientsModel->get(),
        ]);
    }

    public function editclients($id = null)
    {
        $client = $this->clientsModel->getclient($id);

        if (! $client) {
            return redirect()->to(base_url(CLIENTS_CONTROLLER));
        }

        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Editing: ' . html_entity_decode((string) $client['fullName']),
            'user' => $this->adminUser,
            'clients' => $client,
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $rules = [
                'client-name' => 'required',
                'client-email' => 'required|valid_email',
                'client-phone' => 'required|regex_match[/([0-9\\s\\-]{7,})(?:\\s*(?:#|x\\.?|ext\\.?|extension)\\s*(\\d+))?$/]',
            ];

            if ($this->validate($rules)) {
                $this->clientsModel->updateClient($id, [
                    'fullName' => htmlentities((string) $this->request->getPost('client-name')),
                    'email' => htmlentities((string) $this->request->getPost('client-email')),
                    'phone' => $this->request->getPost('client-phone'),
                ]);

                session()->setFlashdata('alert', [
                    'type' => 'alert alert-success',
                    'msg' => 'Successfully updated client.',
                ]);

                return redirect()->to(base_url(CLIENTS_CONTROLLER . '/editclients/' . $id));
            }
        }

        return view('admin/clients/editclients', $data);
    }

    public function deleteclient($id = null, $confirm = false)
    {
        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'All Clients',
            'user' => $this->adminUser,
            'clients' => $this->clientsModel->get(),
        ];

        if ($confirm && ! $data['user']['disabled']) {
            $this->clientsModel->deleteclient($id);
            $data['clients'] = $this->clientsModel->get();
            $data['alert'] = [
                'type' => 'alert alert-success',
                'msg' => 'Successfully delete client.',
            ];
        }

        return view('admin/clients/clients', $data);
    }
}

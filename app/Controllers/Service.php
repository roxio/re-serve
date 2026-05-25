<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\AgentsModel;
use App\Models\MainModel;
use App\Models\ServiceModel;

class Service extends BaseController
{
    private array $pageData;
    private ?array $adminUser;
    private ServiceModel $serviceModel;
    private AgentsModel $agentsModel;

    public function __construct()
    {
        $mainModel = new MainModel();
        $adminModel = new AdminModel();

        $this->serviceModel = new ServiceModel();
        $this->agentsModel = new AgentsModel();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->adminUser = $adminModel->adminDetails();
    }

    public function index()
    {
        return redirect()->to(base_url(SERVICE_CONTROLLER . '/services'));
    }

    public function services()
    {
        return view('admin/service/service', [
            'page_data' => $this->pageData,
            'page_title' => 'All Services',
            'user' => $this->adminUser,
            'services' => $this->serviceModel->serviceList(),
            'agent_List_By_Service' => $this->serviceModel->agentListByService(),
        ]);
    }

    public function addservice()
    {
        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Add Service',
            'user' => $this->adminUser,
            'agents' => $this->agentsModel->agentList(),
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            if ($this->validate($this->serviceRules())) {
                $newService = $this->servicePayload((array) $this->request->getPost('agent'));

                if ($this->attachImage('site-logo', FCPATH . 'uploads/img', 'image', $newService, $data, 'Please must select image file for service.')) {
                    $this->serviceModel->addService($newService);
                    $data['alert'] = [
                        'type' => 'alert alert-success',
                        'msg' => 'Service Added Successfully.',
                    ];
                }
            }
        }

        return view('admin/service/addservice', $data);
    }

    public function editservice($id = null)
    {
        $service = $this->serviceModel->getservice($id);

        if (! $service) {
            return redirect()->to(base_url(SERVICE_CONTROLLER));
        }

        $service['agentIds'] = $service['agentIds'] ? explode(',', $service['agentIds']) : [];
        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Editing: ' . html_entity_decode((string) $service['title']),
            'user' => $this->adminUser,
            'service' => $service,
            'agents' => $this->agentsModel->agentList(),
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            if ($this->validate($this->serviceRules())) {
                $toUpdate = $this->servicePayload((array) $this->request->getPost('agent'));
                $this->attachOptionalImage('site-logo', FCPATH . 'uploads/img', 'image', $toUpdate, $data);
                $this->serviceModel->updateService($id, $toUpdate);

                session()->setFlashdata('alert', [
                    'type' => 'alert alert-success',
                    'msg' => 'Successfully updated service.',
                ]);

                return redirect()->to(base_url(SERVICE_CONTROLLER . '/editservice/' . $id));
            }
        }

        return view('admin/service/editservice', $data);
    }

    public function deleteService($id = null, $confirm = false)
    {
        if ($confirm && ! ($this->adminUser['disabled'] ?? false)) {
            $this->serviceModel->deleteService($id);
            session()->setFlashdata('alert', [
                'type' => 'alert alert-success',
                'msg' => 'Successfully delete service.',
            ]);
        }

        return redirect()->to(base_url(SERVICE_CONTROLLER));
    }

    private function serviceRules(): array
    {
        return [
            'service-title' => 'required',
            'service-content' => 'required',
            'service-price' => 'required|numeric|greater_than[0.99]',
            'service-space' => 'required',
            'service-starts' => 'required',
            'service-ends' => 'required',
            'service-duration' => 'required',
            'agent' => 'required',
        ];
    }

    private function servicePayload(array $agents): array
    {
        return [
            'title' => htmlentities((string) $this->request->getPost('service-title')),
            'description' => htmlentities((string) $this->request->getPost('service-content')),
            'price' => $this->request->getPost('service-price'),
            'servSpace' => $this->request->getPost('service-space'),
            'servStart' => $this->request->getPost('service-starts'),
            'servEnd' => $this->request->getPost('service-ends'),
            'servDuration' => $this->request->getPost('service-duration'),
            'agentIds' => implode(',', $agents),
        ];
    }

    private function attachImage(string $field, string $path, string $target, array &$payload, array &$data, string $missingMessage): bool
    {
        $file = $this->request->getFile($field);

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            $data['logo_error'] = $missingMessage;
            return false;
        }

        return $this->attachOptionalImage($field, $path, $target, $payload, $data);
    }

    private function attachOptionalImage(string $field, string $path, string $target, array &$payload, array &$data): bool
    {
        $file = $this->request->getFile($field);

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        if (! $file->isValid() || ! in_array(strtolower($file->getExtension()), ['gif', 'jpg', 'jpeg', 'png', 'svg'], true)) {
            $data['logo_error'] = 'Only .gif, .jpg, .jpeg, .png, .svg Files are allowed.';
            return false;
        }

        $filename = $file->getRandomName();
        $file->move($path, $filename);
        $payload[$target] = $filename;

        return true;
    }
}

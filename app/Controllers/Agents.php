<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\AgentsModel;
use App\Models\MainModel;

class Agents extends BaseController
{
    private array $pageData;
    private ?array $adminUser;
    private AgentsModel $agentsModel;

    public function __construct()
    {
        $mainModel = new MainModel();
        $adminModel = new AdminModel();

        $this->agentsModel = new AgentsModel();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->adminUser = $adminModel->adminDetails();
    }

    public function index()
    {
        return view('admin/agents/agent', [
            'page_data' => $this->pageData,
            'page_title' => 'All Agents',
            'user' => $this->adminUser,
            'agents' => $this->agentsModel->agentList(),
        ]);
    }

    public function addagent()
    {
        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Add Agent',
            'user' => $this->adminUser,
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            if ($this->validate($this->agentRules())) {
                $newAgent = $this->agentPayload();

                if ($this->attachImage('site-logo', $newAgent, $data, 'Please must select image file for Agent.')) {
                    $this->agentsModel->addAgent($newAgent);
                    $data['alert'] = [
                        'type' => 'alert alert-success',
                        'msg' => 'Agent Added Successfully.',
                    ];
                }
            }
        }

        return view('admin/agents/addagent', $data);
    }

    public function editagent($id = null)
    {
        $agent = $this->agentsModel->getAgent($id);

        if (! $agent) {
            return redirect()->to(base_url(AGENTS_CONTROLLER));
        }

        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Editing: ' . html_entity_decode((string) $agent['agentName']),
            'user' => $this->adminUser,
            'agent' => $agent,
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            if ($this->validate($this->agentRules())) {
                $toUpdate = $this->agentPayload();
                $this->attachOptionalImage('site-logo', $toUpdate, $data);
                $this->agentsModel->updateAgent($id, $toUpdate);

                session()->setFlashdata('alert', [
                    'type' => 'alert alert-success',
                    'msg' => 'Successfully updated agent.',
                ]);

                return redirect()->to(base_url(AGENTS_CONTROLLER . '/editagent/' . $id));
            }
        }

        return view('admin/agents/editagent', $data);
    }

    public function deleteAgent($id = null, $confirm = false)
    {
        if ($confirm && ! ($this->adminUser['disabled'] ?? false)) {
            $this->agentsModel->deleteAgent($id);
            session()->setFlashdata('alert', [
                'type' => 'alert alert-success',
                'msg' => 'Successfully delete agent.',
            ]);
        }

        return redirect()->to(base_url(AGENTS_CONTROLLER));
    }

    private function agentRules(): array
    {
        return [
            'agentName' => 'required',
            'agentDescription' => 'required',
            'experience' => 'required|numeric',
            'totalBookings' => 'required|numeric',
        ];
    }

    private function agentPayload(): array
    {
        return [
            'agentName' => htmlentities((string) $this->request->getPost('agentName')),
            'agentDetail' => htmlentities((string) $this->request->getPost('agentDescription')),
            'experience' => $this->request->getPost('experience'),
            'totalBookings' => $this->request->getPost('totalBookings'),
        ];
    }

    private function attachImage(string $field, array &$payload, array &$data, string $missingMessage): bool
    {
        $file = $this->request->getFile($field);

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            $data['logo_error'] = $missingMessage;
            return false;
        }

        return $this->attachOptionalImage($field, $payload, $data);
    }

    private function attachOptionalImage(string $field, array &$payload, array &$data): bool
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
        $file->move(FCPATH . 'uploads/img/agents', $filename);
        $payload['agentImage'] = $filename;

        return true;
    }
}

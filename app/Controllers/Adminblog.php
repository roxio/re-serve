<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\BlogModel;
use App\Models\MainModel;

class Adminblog extends BaseController
{
    private array $pageData;
    private ?array $adminUser;
    private BlogModel $blogModel;

    public function __construct()
    {
        $mainModel = new MainModel();
        $adminModel = new AdminModel();

        $this->blogModel = new BlogModel();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->adminUser = $adminModel->adminDetails();
    }

    public function index()
    {
        return view('admin/blog/blog', [
            'page_data' => $this->pageData,
            'page_title' => 'All Posts',
            'user' => $this->adminUser,
            'blogLists' => $this->blogModel->blogList(),
            'blogStatus' => $this->blogModel->blogStatus(),
        ]);
    }

    public function blogStatus()
    {
        $status = $this->request->getPost('bstatus') === 'true' ? 1 : 2;
        $this->blogModel->blogStatusSet(['bstatus' => $status]);

        return $this->response->setJSON(['bstatus' => $status]);
    }

    public function add_post()
    {
        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Add Post',
            'user' => $this->adminUser,
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            if ($this->validate(['title' => 'trim|required', 'description' => 'trim|required'])) {
                $payload = $this->postPayload();

                if ($this->attachRequiredImage($payload, $data)) {
                    $now = date('Y-m-d H:i:s');
                    $payload['datetime_added'] = $now;
                    $payload['datetime_updated'] = $now;
                    $payload['permalink'] = $this->uniquePermalink((string) $this->request->getPost('title'));
                    $this->blogModel->add_post($payload);
                    $data['alert'] = [
                        'type' => 'alert alert-success',
                        'msg' => 'Blog post added successfully',
                    ];
                }
            }
        }

        return view('admin/blog/add_post', $data);
    }

    public function edit_post($id = null)
    {
        $postData = $id ? $this->blogModel->get_post($id) : null;

        if (! $postData) {
            return redirect()->to(base_url(ADMINBLOG_CONTROLLER));
        }

        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Edit Blog Post - ' . $postData['title'],
            'user' => $this->adminUser,
            'postData' => $postData,
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            if ($this->validate(['title' => 'trim|required', 'description' => 'trim|required'])) {
                $payload = $this->postPayload();
                $payload['datetime_updated'] = date('Y-m-d H:i:s');
                $payload['image'] = $postData['image'];

                if ($this->attachOptionalImage($payload, $data)) {
                    $permalink = generatePermalink((string) $this->request->getPost('title'));
                    if ($permalink !== $postData['permalink']) {
                        $permalink = $this->uniquePermalink((string) $this->request->getPost('title'));
                    }

                    $payload['permalink'] = $permalink;
                    $this->blogModel->update_post($id, $payload);
                    $data['postData'] = $this->blogModel->get_post($id);
                    $data['alert'] = [
                        'type' => 'alert alert-success',
                        'msg' => 'Blog post updated successfully',
                    ];
                }
            }
        }

        return view('admin/blog/edit_post', $data);
    }

    public function delete_post($id = null, $confirm = false)
    {
        if ($confirm && ! ($this->adminUser['disabled'] ?? false)) {
            $this->blogModel->delete_post($id);
            session()->setFlashdata('alert', [
                'type' => 'alert alert-success',
                'msg' => 'Successfully delete post.',
            ]);
        }

        return redirect()->to(base_url(ADMINBLOG_CONTROLLER));
    }

    private function postPayload(): array
    {
        return [
            'title' => htmlentities((string) $this->request->getPost('title')),
            'description' => htmlentities((string) $this->request->getPost('description')),
            'status' => $this->request->getPost('status') === 'on' ? 1 : 2,
        ];
    }

    private function uniquePermalink(string $title): string
    {
        $base = generatePermalink($title);
        $permalink = $base;
        $index = 1;

        while ($this->blogModel->checkPermalink($permalink) > 0) {
            $permalink = $base . '-' . $index;
            $index++;
        }

        return $permalink;
    }

    private function attachRequiredImage(array &$payload, array &$data): bool
    {
        $file = $this->request->getFile('image');

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            $data['imageError'] = 'Upload Image';
            return false;
        }

        return $this->storeImage($file, $payload, $data);
    }

    private function attachOptionalImage(array &$payload, array &$data): bool
    {
        $file = $this->request->getFile('image');

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        return $this->storeImage($file, $payload, $data);
    }

    private function storeImage($file, array &$payload, array &$data): bool
    {
        if (! $file->isValid() || ! in_array(strtolower($file->getExtension()), ['jpeg', 'jpg', 'png'], true)) {
            $data['imageError'] = 'Only image types allowed';
            return false;
        }

        $filename = uniqid('', true) . '.' . strtolower($file->getExtension());
        $file->move(FCPATH . 'uploads/img/blog', $filename);
        $payload['image'] = $filename;

        return true;
    }
}

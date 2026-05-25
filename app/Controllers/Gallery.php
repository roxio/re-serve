<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\GalleryModel;
use App\Models\MainModel;

class Gallery extends BaseController
{
    private array $pageData;
    private ?array $adminUser;
    private GalleryModel $galleryModel;

    public function __construct()
    {
        $mainModel = new MainModel();
        $adminModel = new AdminModel();

        $this->galleryModel = new GalleryModel();
        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->adminUser = $adminModel->adminDetails();
    }

    public function index()
    {
        return redirect()->to(base_url(GALLERY_CONTROLLER . '/listGallery'));
    }

    public function listGallery()
    {
        return view('admin/gallery/gallery', [
            'page_data' => $this->pageData,
            'page_title' => 'All Gallery Images',
            'user' => $this->adminUser,
            'listGalleryWidCat' => $this->galleryModel->listGalleryWidCat(),
        ]);
    }

    public function addImg()
    {
        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Add Gallery Image',
            'user' => $this->adminUser,
            'categories' => $this->galleryModel->listCat(),
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            if ($this->validate($this->imageRules())) {
                $newImage = $this->imagePayload();

                if ($this->attachImage('gImage', $newImage, $data, 'Please select Image.')) {
                    $this->galleryModel->setGallery($newImage);
                    $data['alert'] = [
                        'type' => 'alert alert-success',
                        'msg' => 'Gallery image added successfully.',
                    ];
                }
            }
        }

        return view('admin/gallery/addGallery', $data);
    }

    public function editImg($id = null)
    {
        $gallery = $this->galleryModel->getGallery($id);

        if (! $gallery) {
            return redirect()->to(base_url(GALLERY_CONTROLLER));
        }

        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Editing: ' . html_entity_decode((string) $gallery['imgName']),
            'user' => $this->adminUser,
            'gallery' => $gallery,
            'listGalleryWidCat' => $this->galleryModel->listGalleryWidCat(),
            'categories' => $this->galleryModel->listCat(),
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            if ($this->validate($this->imageRules())) {
                $toUpdate = $this->imagePayload();
                $this->attachOptionalImage('gImage', $toUpdate, $data);
                $this->galleryModel->updateGallery($id, $toUpdate);
                session()->setFlashdata('alert', [
                    'type' => 'alert alert-success',
                    'msg' => 'Successfully updated image.',
                ]);

                return redirect()->to(base_url(GALLERY_CONTROLLER . '/editImg/' . $id));
            }
        }

        return view('admin/gallery/editGallery', $data);
    }

    public function deleteImg($id = null, $confirm = false)
    {
        if ($confirm && ! ($this->adminUser['disabled'] ?? false)) {
            $this->galleryModel->deleteGallery($id);
            session()->setFlashdata('alert', [
                'type' => 'alert alert-success',
                'msg' => 'Successfully delete Image.',
            ]);
        }

        return redirect()->to(base_url(GALLERY_CONTROLLER));
    }

    public function categories()
    {
        return view('admin/gallery/galleryCat', [
            'page_data' => $this->pageData,
            'page_title' => 'All Categories',
            'user' => $this->adminUser,
            'categories' => $this->galleryModel->listCat(),
        ]);
    }

    public function catAdd()
    {
        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Add Category',
            'user' => $this->adminUser,
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            if ($this->validate(['category-name' => 'required'])) {
                $this->galleryModel->setCat([
                    'cName' => htmlentities((string) $this->request->getPost('category-name')),
                ]);
                $data['alert'] = [
                    'type' => 'alert alert-success',
                    'msg' => 'Category Added Successfully.',
                ];
            }
        }

        return view('admin/gallery/addGalleryCat', $data);
    }

    public function catEdit($id = null)
    {
        $category = $this->galleryModel->getCat($id);

        if (! $category) {
            return redirect()->to(base_url(GALLERY_CONTROLLER . '/categories'));
        }

        $data = [
            'page_data' => $this->pageData,
            'page_title' => 'Editing: ' . html_entity_decode((string) $category['cName']),
            'user' => $this->adminUser,
            'categories' => $category,
        ];

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            if ($this->validate(['category-name' => 'required'])) {
                $this->galleryModel->updateCat($id, [
                    'cName' => htmlentities((string) $this->request->getPost('category-name')),
                ]);
                session()->setFlashdata('alert', [
                    'type' => 'alert alert-success',
                    'msg' => 'Successfully updated category.',
                ]);

                return redirect()->to(base_url(GALLERY_CONTROLLER . '/catEdit/' . $id));
            }
        }

        return view('admin/gallery/editGalleryCat', $data);
    }

    public function catDelete($id = null, $confirm = false)
    {
        if ($confirm && ! ($this->adminUser['disabled'] ?? false)) {
            $this->galleryModel->deleteCat($id);
            session()->setFlashdata('alert', [
                'type' => 'alert alert-success',
                'msg' => 'Successfully delete category.',
            ]);
        }

        return redirect()->to(base_url(GALLERY_CONTROLLER . '/categories'));
    }

    private function imageRules(): array
    {
        return [
            'image-title' => 'required',
            'image-content' => 'required',
            'categoryId' => 'required',
        ];
    }

    private function imagePayload(): array
    {
        return [
            'imgName' => htmlentities((string) $this->request->getPost('image-title')),
            'imgDetails' => htmlentities((string) $this->request->getPost('image-content')),
            'catId' => $this->request->getPost('categoryId'),
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
        $file->move(FCPATH . 'uploads/gallery', $filename);
        $payload['imgPath'] = $filename;

        return true;
    }
}

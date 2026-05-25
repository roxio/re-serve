<?php

namespace App\Controllers;

use App\Models\AdsModel;
use App\Models\AdminModel;
use App\Models\AnalyticsModel;
use App\Models\CommentSettingsModel;
use App\Models\MainModel;
use App\Models\MetaModel;
use App\Models\PageModel;
use App\Models\RecaptchaModel;
use App\Models\SmtpModel;
use App\Models\SocialKeysModel;

class Layout extends BaseController
{
    private array $pageData;
    private ?array $adminUser;

    public function __construct()
    {
        $mainModel = new MainModel();
        $adminModel = new AdminModel();

        $this->pageData = $mainModel->pageData();
        $this->pageData['update'] = $mainModel->updates_settings();
        $this->adminUser = $adminModel->adminDetails();
    }

    public function index()
    {
        return redirect()->to(base_url(LAYOUT_CONTROLLER . '/pages'));
    }

    public function social_keys()
    {
        $model = new SocialKeysModel();
        $this->pageData['social_keys'] = $model->get();
        $data = $this->viewData('Social Login Keys');

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $model->updateSettings([
                'google_public' => $this->nullablePost('google-public'),
                'google_secret' => $this->nullablePost('google-secret'),
                'facebook_public' => $this->nullablePost('facebook-public'),
                'facebook_secret' => $this->nullablePost('facebook-secret'),
            ]);

            $data['page_data']['social_keys'] = $model->get();
            $data['alert'] = [
                'type' => 'alert alert-success',
                'msg' => 'Social Login API Keys updated successfully.',
            ];
        }

        return view('admin/layout/social_keys', $data);
    }

    public function analytics()
    {
        $data = $this->viewData('Analytics');

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $model = new AnalyticsModel();
            $model->updateSettings([
                'code' => htmlentities((string) $this->request->getPost('site-analytics')),
            ]);

            $data['page_data']['analytics'] = $model->get();
            $data['alert'] = [
                'type' => 'alert alert-success',
                'msg' => 'Analytics Code updated successfully',
            ];
        }

        return view('admin/layout/analytics', $data);
    }

    public function recaptcha()
    {
        $data = $this->viewData('Recaptcha');

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $status = $this->request->getPost('site-status') ? 1 : 0;
            $rules = $status ? [
                'site-key' => 'required',
                'secret-key' => 'required',
            ] : [];

            if ($rules === [] || $this->validate($rules)) {
                $model = new RecaptchaModel();
                $model->updateSettings([
                    'status' => $status,
                    'site_key' => $this->request->getPost('site-key') ?: '',
                    'secret_key' => $this->request->getPost('secret-key') ?: '',
                ]);

                $data['page_data']['recaptcha'] = $model->get();
                $data['alert'] = [
                    'type' => 'alert alert-success',
                    'msg' => 'Recaptcha Settings updated successfully',
                ];
            }
        }

        return view('admin/layout/recaptcha', $data);
    }

    public function email()
    {
        $smtpModel = new SmtpModel();
        $this->pageData['email'] = $smtpModel->get();
        $data = $this->viewData('E-Mail Settings', [
            'load_scripts' => ['js/includes/email_settings.js'],
        ]);

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $status = $this->request->getPost('site-smtp-status') ? 1 : 0;
            $rules = [
                'site-smtp-email' => 'required|valid_email',
            ];

            if ($status) {
                $rules += [
                    'site-smtp-host' => 'required',
                    'site-smtp-port' => 'required|numeric',
                    'site-smtp-username' => 'required',
                    'site-smtp-password' => 'required',
                ];
            }

            if ($this->validate($rules)) {
                $toUpdate = [
                    'email' => strtolower((string) $this->request->getPost('site-smtp-email')),
                    'status' => $status,
                    'host' => $status ? strtolower((string) $this->request->getPost('site-smtp-host')) : null,
                    'port' => $status ? $this->request->getPost('site-smtp-port') : null,
                    'username' => $status ? $this->request->getPost('site-smtp-username') : null,
                    'password' => $status ? $this->request->getPost('site-smtp-password') : null,
                ];

                $smtpModel->updateSettings($toUpdate);
                $data['page_data']['email'] = $smtpModel->get();
                $data['alert'] = [
                    'type' => 'alert alert-success',
                    'msg' => 'E-Mail settings updated successfully',
                ];
            } else {
                $data['alert'] = [
                    'type' => 'alert alert-danger',
                    'msg' => 'Please fix the following errors below.',
                ];
            }
        }

        return view('admin/layout/email', $data);
    }

    public function meta_tags()
    {
        $data = $this->viewData('Meta Tags');

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $tags = (string) $this->request->getPost('site-meta-tags');
            $newTags = '';

            if ($tags !== '') {
                preg_match_all('~<meta\s+name=[\'"][^\'"]+[\'"]\s+content=[\'"][^\'"]*[\'"]\s*/?>~i', $tags, $matches);
                $newTags = implode('', $matches[0] ?? []);

                if ($newTags === '') {
                    $data['alert'] = [
                        'type' => 'alert alert-danger',
                        'msg' => 'Please type a Meta tag with (name and content) attribute.',
                    ];

                    return view('admin/layout/meta_tags', $data);
                }
            }

            $model = new MetaModel();
            $model->updateSettings(['meta_tags' => $newTags]);
            $data['page_data']['meta_tags'] = $model->get();
            $data['alert'] = [
                'type' => 'alert alert-success',
                'msg' => 'Meta Tags updated successfully',
            ];
        }

        return view('admin/layout/meta_tags', $data);
    }

    public function ads()
    {
        $data = $this->viewData('Ad Settings');

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $topStatus = $this->request->getPost('site-top-ad-status') ? 1 : 0;
            $bottomStatus = $this->request->getPost('site-bottom-ad-status') ? 1 : 0;
            $topCode = trim((string) $this->request->getPost('site-top-ad-code'));
            $bottomCode = trim((string) $this->request->getPost('site-bottom-ad-code'));

            if ($topStatus && ! filter_var($topCode, FILTER_VALIDATE_URL)) {
                $data['alert'] = ['type' => 'alert alert-danger', 'msg' => 'Top Image Ad code is not URL.'];
                return view('admin/layout/ads', $data);
            }

            if ($bottomStatus && ! filter_var($bottomCode, FILTER_VALIDATE_URL)) {
                $data['alert'] = ['type' => 'alert alert-danger', 'msg' => 'Bottom Image Ad code is not URL.'];
                return view('admin/layout/ads', $data);
            }

            $model = new AdsModel();
            $model->updateSettings([
                'top_ad_status' => $topStatus,
                'bottom_ad_status' => $bottomStatus,
                'top_ad' => $topStatus ? $topCode : '',
                'bottom_ad' => $bottomStatus ? $bottomCode : '',
            ]);

            $data['page_data']['ads'] = $model->get();
            $data['alert'] = [
                'type' => 'alert alert-success',
                'msg' => 'Ad settings updated successfully.',
            ];
        }

        return view('admin/layout/ads', $data);
    }

    public function pages()
    {
        $pageModel = new PageModel();

        return view('admin/layout/pages/main', $this->viewData('Page Settings', [
            'all_pages' => $pageModel->get(),
            'load_scripts' => [
                'js/includes/sortables.min.js',
                'js/includes/sortable_list.js',
            ],
        ]));
    }

    public function edit_page($permalink = null)
    {
        $pageModel = new PageModel();
        $page = $permalink ? $pageModel->get_page($permalink) : null;

        if (! $page) {
            return redirect()->to(base_url(LAYOUT_CONTROLLER . '/pages'));
        }

        $data = $this->viewData('Editing ' . html_entity_decode((string) $page['title']), [
            'page' => $page,
        ]);

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $newPermalink = strtolower((string) $this->request->getPost('page-permalink'));
            $rules = $this->pageRules();

            if ($newPermalink !== $page['permalink']) {
                $rules['page-permalink'] = 'required|is_unique[pages.permalink]|alpha_dash';
            }

            if ($this->validate($rules)) {
                $toUpdate = $this->pagePayload($newPermalink, (bool) $this->request->getPost('page-status'));
                $pageModel->set_page($page['permalink'], $toUpdate);
                session()->setFlashdata('alert', [
                    'type' => 'alert alert-success',
                    'msg' => 'Successfully updated Page.',
                ]);

                return redirect()->to(base_url(LAYOUT_CONTROLLER . '/edit_page/' . $toUpdate['permalink']));
            }
        }

        return view('admin/layout/pages/edit_page', $data);
    }

    public function create_page()
    {
        $data = $this->viewData('Create A New Page');

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $title = (string) $this->request->getPost('page-title');
            $permalink = (string) $this->request->getPost('page-permalink');
            $rules = $this->pageRules();

            if ($permalink !== '') {
                $rules['page-permalink'] = 'required|is_unique[pages.permalink]|alpha_dash';
            } else {
                $permalink = securePermalink($title);
            }

            if ($this->validate($rules)) {
                $pageModel = new PageModel();
                $newPage = $this->pagePayload(strtolower($permalink), true);
                $newPage['page_order'] = $pageModel->get_new_page_order();
                $pageModel->create_page($newPage);
                session()->setFlashdata('alert', [
                    'type' => 'alert alert-success',
                    'msg' => 'New page created successfully.',
                ]);

                return redirect()->to(base_url(LAYOUT_CONTROLLER . '/edit_page/' . strtolower($permalink)));
            }
        }

        return view('admin/layout/pages/create_page', $data);
    }

    public function delete_page($permalink = null, $confirm = false)
    {
        $pageModel = new PageModel();
        $page = $permalink ? $pageModel->get_page($permalink) : null;

        if (! $page) {
            return redirect()->to(base_url(LAYOUT_CONTROLLER . '/pages'));
        }

        if ($confirm && ! ($this->adminUser['disabled'] ?? false)) {
            $pageModel->delete_page($permalink);
            return redirect()->to(base_url(LAYOUT_CONTROLLER . '/pages'));
        }

        return view('admin/layout/pages/delete_page', $this->viewData('Delete ' . html_entity_decode((string) $page['title']), [
            'page' => $page,
        ]));
    }

    public function update_page_order()
    {
        $order = $this->request->getPost('order');

        if ($order && $this->request->getPost('ref')) {
            $pageModel = new PageModel();
            $result = $pageModel->set_order(json_decode((string) $order, true) ?: []);

            return $this->response->setJSON(['success' => (bool) $result]);
        }

        return $this->response->setJSON(['success' => false]);
    }

    public function comment_settings()
    {
        $commentModel = new CommentSettingsModel();
        $this->pageData['comment_settings'] = $commentModel->get();
        $data = $this->viewData('Comment Settings', [
            'load_scripts' => ['js/includes/comment_settings.js'],
        ]);

        if ($this->request->getPost('submit') && ! $data['user']['disabled']) {
            $activePlugin = $this->request->getPost('active-plugin');
            $rules = [];

            if ((string) $activePlugin === '1') {
                $rules['facebook-app-id'] = 'required';
            } elseif ((string) $activePlugin === '2') {
                $rules['disqus-short-name'] = 'required';
            }

            if ($rules === [] || $this->validate($rules)) {
                $toUpdate = ['active_plugin' => $activePlugin];

                if ((string) $activePlugin === '1') {
                    $toUpdate['facebook_app_id'] = $this->request->getPost('facebook-app-id');
                } else {
                    $toUpdate['disqus_short_name'] = $this->request->getPost('disqus-short-name');
                }

                $commentModel->updateSettings($toUpdate);
                $data['page_data']['comment_settings'] = $commentModel->get();
                $data['alert'] = [
                    'type' => 'alert alert-success',
                    'msg' => 'Comment settings updated successfully',
                ];
            } else {
                $data['alert'] = [
                    'type' => 'alert alert-danger',
                    'msg' => 'Please fix the following errors below.',
                ];
            }
        }

        return view('admin/layout/comment_settings', $data);
    }

    private function viewData(string $title, array $extra = []): array
    {
        return array_merge([
            'page_data' => $this->pageData,
            'page_title' => $title,
            'user' => $this->adminUser,
        ], $extra);
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value !== '' ? $value : null;
    }

    private function pageRules(): array
    {
        return [
            'page-title' => 'required',
            'page-content' => 'required',
            'page-position' => 'required|in_list[1,2,3]',
        ];
    }

    private function pagePayload(string $permalink, bool $status): array
    {
        return [
            'title' => htmlentities((string) $this->request->getPost('page-title')),
            'content' => htmlentities((string) $this->request->getPost('page-content')),
            'permalink' => strtolower($permalink),
            'position' => $this->request->getPost('page-position'),
            'status' => $status ? 1 : 0,
        ];
    }
}

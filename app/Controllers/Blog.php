<?php

namespace App\Controllers;

use App\Models\BlogModel;
use App\Models\MainModel;

class Blog extends BaseController
{
    public function index($permalinkOrPage = null)
    {
        $blogModel = new BlogModel();
        $status = $blogModel->blogStatus();

        if (($status['bstatus'] ?? '0') != '1') {
            return redirect()->to(base_url('404'));
        }

        $mainModel = new MainModel();
        $pageData = $mainModel->pageData();
        $theme = $pageData['theme'] ?? 'redishtheme';

        if ($permalinkOrPage && ! ctype_digit((string) $permalinkOrPage)) {
            $post = $blogModel->get_post_by_permalink($permalinkOrPage);

            if (! $post) {
                return redirect()->to(base_url('404'));
            }

            return view('themes/' . $theme . '/single', array_merge($pageData, [
                'permalink' => $permalinkOrPage,
                'post' => $post,
                'comment_settings' => $blogModel->commentSettings() ?? [
                    'active_plugin' => 2,
                    'facebook_app_id' => '',
                    'disqus_short_name' => '',
                ],
                'session' => session(),
            ]));
        }

        $perPage = 12;
        $page = max(1, (int) ($permalinkOrPage ?: 1));
        $offset = ($page - 1) * $perPage;
        $totalRows = $blogModel->num_rows();

        return view('themes/' . $theme . '/blog', array_merge($pageData, [
            'blogList' => $blogModel->blogListu($perPage, $offset),
            'pagination' => pagination_links(base_url(BLOG_CONTROLLER), $totalRows, $perPage, $page),
            'session' => session(),
        ]));
    }
}

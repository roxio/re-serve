<?php

namespace App\Controllers;

use App\Models\MainModel;
use App\Models\PageModel;

class Page extends BaseController
{
    public function index($permalink = null)
    {
        $page = $permalink ? (new PageModel())->get_page($permalink) : null;

        if (! $page) {
            return redirect()->to(base_url());
        }

        $mainModel = new MainModel();
        $pageData = $mainModel->pageData();

        return view('themes/' . ($pageData['theme'] ?? 'redishtheme') . '/page', array_merge($pageData, [
            'page' => $page,
            'session' => session(),
        ]));
    }
}

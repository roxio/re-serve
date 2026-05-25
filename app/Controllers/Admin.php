<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function index()
    {
        return redirect()->to(base_url(GENERAL_CONTROLLER));
    }
}

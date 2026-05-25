<?php

namespace App\Controllers;

class Install extends BaseController
{
    public function index()
    {
        return redirect()->to(base_url('auth/login'));
    }

    public function requirements()
    {
        return $this->index();
    }

    public function database()
    {
        return $this->index();
    }

    public function settings()
    {
        return $this->index();
    }

    public function finish()
    {
        return $this->index();
    }
}

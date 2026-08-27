<?php

namespace App\Portals\WebV1\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;

class HomeController extends BaseController
{
    public function index(): View
    {
        return view('webv1::home');
    }
}

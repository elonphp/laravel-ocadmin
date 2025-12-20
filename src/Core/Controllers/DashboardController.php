<?php

namespace Elonphp\LaravelOcadminModules\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request): View
    {
        return view('ocadmin::common.dashboard');
    }
}

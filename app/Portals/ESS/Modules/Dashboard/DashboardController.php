<?php

namespace App\Portals\ESS\Modules\Dashboard;

use App\Portals\ESS\Core\Controllers\EssController;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends EssController
{
    public function index(): Response
    {
        return Inertia::render('Dashboard');
    }
}

<?php

namespace Elonphp\LaravelOcadminModules\Core\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected array $breadcrumbs = [];

    public function __construct()
    {
        $this->setBreadcrumbs();
    }

    /**
     * Set breadcrumbs for this controller.
     * Override in child controllers to customize.
     */
    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => __('ocadmin::common.dashboard'),
                'href' => ocadmin_route('dashboard'),
            ],
        ];
    }

    /**
     * Get breadcrumbs array.
     */
    protected function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }
}

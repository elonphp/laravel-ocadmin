<?php

namespace App\Portals\Ocadmin\Core\ViewComposers;

use App\Helpers\Classes\LocaleHelper;
use Illuminate\View\View;

class LocaleComposer
{
    public function compose(View $view): void
    {
        $view->with('locales', LocaleHelper::getSupportedLocales());
        $view->with('localeNames', LocaleHelper::getLocaleNames());
    }
}

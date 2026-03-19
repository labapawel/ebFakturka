<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application language.
     *
     * @param  string  $lang
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch($lang)
    {
        // Get all available directories in resources/lang
        $availableLangs = array_map('basename', array_filter(glob(resource_path('lang/*')), 'is_dir'));

        if (in_array($lang, $availableLangs)) {
            Session::put('applocale', $lang);
        }

        return redirect()->back();
    }
}

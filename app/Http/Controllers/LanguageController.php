<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    protected $availableLocales = ['en', 'uk', 'pl'];

    public function switch($locale)
    {
        if (!in_array($locale, $this->availableLocales)) {
            return back()->withErrors(['message' => __('messages.invalid_locale')]);
        }

        // Clear session and set new locale
        Session::forget('locale');
        Session::put('locale', $locale);
        App::setLocale($locale);

        return back()->with('success', __('messages.language_switched'));
    }
} 
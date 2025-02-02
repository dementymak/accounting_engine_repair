<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageMiddleware
{
    protected $availableLocales = ['en', 'uk', 'pl'];

    public function handle(Request $request, Closure $next)
    {
        // Get locale from session or URL parameter
        $locale = $request->segment(2) === 'language' ? $request->segment(3) : Session::get('locale');
        
        // If no locale is set, use browser preference or fallback
        if (!$locale) {
            $locale = $request->getPreferredLanguage($this->availableLocales) ?? config('app.fallback_locale', 'en');
        }

        // Ensure locale is valid
        if (!in_array($locale, $this->availableLocales)) {
            $locale = config('app.fallback_locale', 'en');
        }

        // Store locale in session and set application locale
        Session::put('locale', $locale);
        App::setLocale($locale);
        
        return $next($request);
    }
} 
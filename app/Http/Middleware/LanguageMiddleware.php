<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            // Get default locale from config
            $locale = config('app.locale', 'en');
            Session::put('locale', $locale);
            App::setLocale($locale);
        }
        return $next($request);
    }
} 
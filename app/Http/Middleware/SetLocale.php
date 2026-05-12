<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $allowed = ['uz', 'ru', 'en'];
        $locale  = session('locale', 'uz');

        if (!in_array($locale, $allowed)) {
            $locale = 'uz';
        }

        App::setLocale($locale);

        return $next($request);
    }
}

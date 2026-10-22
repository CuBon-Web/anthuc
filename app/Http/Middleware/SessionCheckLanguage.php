<?php

namespace App\Http\Middleware;

use Closure;

class SessionCheckLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $request->session()->get('locale');
        if (!$locale) {
            $locale = app()->getLocale();
            $request->session()->put('locale', $locale);
        }
        app()->setLocale($locale);

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale') ?: Locale::Vietnamese->value;
        $supportedLocales = collect(Locale::cases())
            ->pluck('value')
            ->all();

        abort_unless(in_array($locale, $supportedLocales, true), 404);

        App::setLocale($locale);

        return $next($request);
    }
}

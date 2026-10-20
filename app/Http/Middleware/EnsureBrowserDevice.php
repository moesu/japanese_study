<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\BrowserDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureBrowserDevice
{
    /**
     * Give the browser a stable id and record when that visitor was last active.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $generated = BrowserDevice::id($request) === null;
        if ($generated) {
            $request->cookies->set('n5_device', (string) Str::uuid());
        }

        $device = BrowserDevice::id($request);
        if ($device !== null && ! $request->is('admin', 'admin/*')) {
            User::forDevice($device)->touchActivity();
        }

        $response = $next($request);

        if ($generated) {
            $response->headers->setCookie(cookie(
                'n5_device',
                (string) $request->cookie('n5_device'),
                60 * 24 * 365,
                '/',
                null,
                $request->isSecure(),
                false,
                false,
                'lax',
            ));
        }

        return $response;
    }
}

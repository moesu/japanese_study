<?php

namespace App\Http\Middleware;

use App\Support\AdminGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(AdminGate::enabled(), 404);

        if (! AdminGate::check($request)) {
            return redirect()->guest(route('admin.login'));
        }

        return $next($request);
    }
}

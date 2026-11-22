<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Peran
{
    /** Pakai: ->middleware('peran:admin,verifikator') */
    public function handle(Request $request, Closure $next, string ...$role)
    {
        $user = $request->user();
        abort_unless($user && $user->aktif && $user->punya(...$role), 403, 'Anda tidak punya akses ke halaman ini.');

        return $next($request);
    }
}

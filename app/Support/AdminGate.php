<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminGate
{
    public const SESSION_KEY = 'admin_authenticated';

    public static function enabled(): bool
    {
        return Setting::adminPasswordHash() !== null;
    }

    public static function check(Request $request): bool
    {
        return self::enabled() && $request->session()->get(self::SESSION_KEY) === true;
    }

    public static function attempt(string $password): bool
    {
        $hash = Setting::adminPasswordHash();

        if ($hash === null) {
            return false;
        }

        return Hash::check($password, $hash);
    }

    public static function grant(Request $request): void
    {
        $request->session()->put(self::SESSION_KEY, true);
        $request->session()->regenerate();
    }

    public static function revoke(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->regenerate();
    }
}

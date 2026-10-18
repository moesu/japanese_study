<?php

namespace App\Support;

use Illuminate\Http\Request;

class BrowserDevice
{
    public static function id(Request $request): ?string
    {
        $id = (string) $request->cookie('n5_device', '');

        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id) === 1
            ? $id
            : null;
    }
}

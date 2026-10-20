<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Setting extends Model
{
    public const ADMIN_PASSWORD = 'admin_password';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = static::query()->where('key', $key)->value('value');

        return is_string($value) ? $value : $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    public static function adminPasswordHash(): ?string
    {
        $hash = static::get(self::ADMIN_PASSWORD);

        return is_string($hash) && $hash !== '' ? $hash : null;
    }

    public static function setAdminPassword(string $password): void
    {
        static::put(self::ADMIN_PASSWORD, Hash::make($password));
    }
}

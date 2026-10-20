<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

#[Fillable(['user_id', 'path', 'method', 'visited_at'])]
class PageVisit extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(User $user, Request $request): void
    {
        if (! $request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return;
        }

        if ($request->is('admin', 'admin/*', 'up')) {
            return;
        }

        $path = '/'.ltrim($request->path(), '/');
        if ($request->getQueryString()) {
            $path .= '?'.$request->getQueryString();
        }

        if (strlen($path) > 500) {
            $path = substr($path, 0, 500);
        }

        $recent = static::query()
            ->where('user_id', $user->id)
            ->where('path', $path)
            ->where('visited_at', '>=', now()->subSeconds(30))
            ->exists();

        if ($recent) {
            return;
        }

        static::query()->create([
            'user_id' => $user->id,
            'path' => $path,
            'method' => 'GET',
            'visited_at' => now(),
        ]);
    }
}

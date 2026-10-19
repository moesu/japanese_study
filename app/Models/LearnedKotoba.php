<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

#[Fillable(['user_id', 'word', 'reading', 'learned'])]
class LearnedKotoba extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'learned' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return Collection<string, true>
     */
    public static function learnedKeysFor(?User $user): Collection
    {
        if ($user === null) {
            return collect();
        }

        return static::query()
            ->where('user_id', $user->id)
            ->where('learned', true)
            ->get(['word', 'reading'])
            ->mapWithKeys(fn (self $row): array => [$row->word.'|'.$row->reading => true]);
    }

    public static function toggle(User $user, string $word, string $reading): bool
    {
        $row = static::query()->firstOrNew([
            'user_id' => $user->id,
            'word' => $word,
            'reading' => $reading,
        ]);
        $row->learned = ! ($row->exists && $row->learned);
        $row->save();

        return $row->learned;
    }
}

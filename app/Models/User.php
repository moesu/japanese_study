<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'device'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public static function findForDevice(?string $device): ?self
    {
        if ($device === null) {
            return null;
        }

        return static::query()->where('device', $device)->first();
    }

    public static function forDevice(string $device): self
    {
        return static::query()->firstOrCreate(
            ['device' => $device],
            [
                'name' => 'Learner',
                'email' => $device.'@learners.local',
                'password' => Str::password(40),
            ],
        );
    }

    /**
     * @return HasMany<LearnedKotoba, $this>
     */
    public function learnedKotobas(): HasMany
    {
        return $this->hasMany(LearnedKotoba::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

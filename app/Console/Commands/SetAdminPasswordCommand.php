<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('admin:password {password? : The new admin password}')]
#[Description('Store or replace the hashed admin panel password in the database')]
class SetAdminPasswordCommand extends Command
{
    public function handle(): int
    {
        $password = $this->argument('password') ?? $this->secret('Admin password');

        if (! is_string($password) || $password === '') {
            $this->error('Password cannot be empty.');

            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        Setting::setAdminPassword($password);
        $this->info('Admin password saved to the database.');

        return self::SUCCESS;
    }
}

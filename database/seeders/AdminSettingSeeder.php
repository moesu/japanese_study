<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AdminSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (Setting::adminPasswordHash() !== null) {
            return;
        }

        $password = env('ADMIN_PASSWORD');
        if (! is_string($password) || $password === '') {
            return;
        }

        Setting::setAdminPassword($password);
    }
}

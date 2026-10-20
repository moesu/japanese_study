<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_is_hidden_when_password_is_unset(): void
    {
        $this->get(route('admin.login'))->assertNotFound();
        $this->get(route('admin.index'))->assertNotFound();
    }

    public function test_admin_login_shows_device_totals_and_last_active_time(): void
    {
        Setting::setAdminPassword('secret-admin');

        $this->withUnencryptedCookie('n5_device', '11111111-1111-4111-8111-111111111111')
            ->get(route('home'))
            ->assertOk();

        $learner = User::query()->where('device', '11111111-1111-4111-8111-111111111111')->first();
        $this->assertNotNull($learner);
        $this->assertNotNull($learner->last_active_at);

        $this->get(route('admin.index'))
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.login.store'), ['password' => 'wrong'])
            ->assertRedirect()
            ->assertSessionHasErrors('password');

        $this->post(route('admin.login.store'), ['password' => 'secret-admin'])
            ->assertRedirect(route('admin.index'));

        $this->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Total devices: 1')
            ->assertSee('SR No')
            ->assertSee('>1<', false)
            ->assertSee('11111111-1111')
            ->assertSee($learner->last_active_at->timezone('Asia/Bangkok')->format('Y-m-d h:i A'));
    }

    public function test_admin_devices_are_paginated_with_serial_numbers(): void
    {
        Setting::setAdminPassword('secret-admin');

        foreach (range(1, 21) as $index) {
            User::factory()->create([
                'device' => sprintf('11111111-1111-4111-8111-%012d', $index),
                'last_active_at' => now()->subMinutes($index),
            ]);
        }

        $this->post(route('admin.login.store'), ['password' => 'secret-admin'])
            ->assertRedirect(route('admin.index'));

        $this->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Total devices: 21')
            ->assertSee('>1<', false)
            ->assertSee('>20<', false)
            ->assertDontSee('>21<', false)
            ->assertSee('page=2', false);

        $this->get(route('admin.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('>21<', false);
    }

    public function test_admin_password_command_stores_a_hash_in_the_database(): void
    {
        $this->artisan('admin:password', ['password' => 'database-secret'])
            ->assertSuccessful();

        $this->assertNotNull(Setting::adminPasswordHash());
        $this->assertDatabaseHas('settings', ['key' => 'admin_password']);

        $this->post(route('admin.login.store'), ['password' => 'database-secret'])
            ->assertRedirect(route('admin.index'));
    }
}

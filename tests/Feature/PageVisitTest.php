<?php

namespace Tests\Feature;

use App\Models\PageVisit;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageVisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_visits_are_recorded_with_url_and_time_for_admin(): void
    {
        Setting::setAdminPassword('secret-admin');

        $this->withUnencryptedCookie('n5_device', '11111111-1111-4111-8111-111111111111')
            ->get(route('home'))
            ->assertOk();

        $learner = User::query()->where('device', '11111111-1111-4111-8111-111111111111')->firstOrFail();
        $visit = PageVisit::query()->where('user_id', $learner->id)->first();
        $this->assertNotNull($visit);
        $this->assertSame('/', $visit->path);

        $this->post(route('admin.login.store'), ['password' => 'secret-admin'])
            ->assertRedirect(route('admin.index'));

        $time = $visit->visited_at->timezone('Asia/Bangkok')->format('Y-m-d h:i A');

        $this->get(route('admin.index'))
            ->assertOk()
            ->assertSee('>/</', false)
            ->assertSee($time);

        $this->get(route('admin.devices.show', $learner))
            ->assertOk()
            ->assertSee('URL access')
            ->assertSee('>/</', false)
            ->assertSee($time);

        $this->assertMatchesRegularExpression('/\b(AM|PM)\b/', $time);
    }
}

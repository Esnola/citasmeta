<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_users_can_view_the_home_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('WhatsApp Scheduler');
    }

    public function test_authenticated_users_are_redirected_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}

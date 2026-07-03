<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppMessageClientLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_messages_page_has_been_removed(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get('/messages')
            ->assertNotFound();
    }
}

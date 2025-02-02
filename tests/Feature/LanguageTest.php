<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_switch_language()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('language.switch', ['locale' => 'uk']));

        $response->assertSessionHas('locale', 'uk');
        $response->assertRedirect();
    }

    public function test_invalid_language_is_rejected()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('language.switch', ['locale' => 'invalid-locale']));

        $response->assertSessionHasErrors();
    }

    public function test_language_persists_across_requests()
    {
        $this->actingAs($this->user);

        // Set language to Ukrainian
        $this->get(route('language.switch', ['locale' => 'uk']));

        // Visit repair cards page
        $response = $this->get(route('repair-cards.index'));
        
        // Assert Ukrainian translations are present
        $response->assertSee('Картки ремонту');
        $response->assertSee('Додати нову');
    }

    public function test_default_language_is_english()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('repair-cards.index'));
        
        $response->assertSee('Repair Cards');
        $response->assertSee('Add New');
    }
} 
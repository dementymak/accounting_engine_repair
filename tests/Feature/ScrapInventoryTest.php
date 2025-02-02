<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ScrapInventory;
use App\Models\ScrapTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrapInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_add_initial_balance()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('scrap.add-initial'), [
            'weight' => 10.5,
            'notes' => 'Initial scrap balance'
        ]);

        $response->assertRedirect(route('scrap.index'));
        $this->assertDatabaseHas('scrap_inventory', [
            'weight' => 10.5
        ]);
        $this->assertDatabaseHas('scrap_transactions', [
            'type' => 'initial',
            'weight' => 10.5,
            'notes' => 'Initial scrap balance'
        ]);
    }

    public function test_user_can_write_off_scrap()
    {
        $this->actingAs($this->user);

        ScrapInventory::factory()->create([
            'weight' => 15.5
        ]);

        $response = $this->post(route('scrap.writeoff'), [
            'weight' => 5.5,
            'notes' => 'Write-off test'
        ]);

        $response->assertRedirect(route('scrap.index'));
        $this->assertDatabaseHas('scrap_inventory', [
            'weight' => 10.0
        ]);
        $this->assertDatabaseHas('scrap_transactions', [
            'type' => 'writeoff',
            'weight' => -5.5,
            'notes' => 'Write-off test'
        ]);
    }

    public function test_cannot_write_off_more_than_available()
    {
        $this->actingAs($this->user);

        ScrapInventory::factory()->create([
            'weight' => 5.0
        ]);

        $response = $this->post(route('scrap.writeoff'), [
            'weight' => 10.0
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('scrap_inventory', [
            'weight' => 5.0
        ]);
    }

    public function test_scrap_weight_must_be_positive()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('scrap.add-initial'), [
            'weight' => -5.0
        ]);

        $response->assertSessionHasErrors('weight');
    }

    public function test_scrap_transactions_are_properly_tracked()
    {
        $this->actingAs($this->user);

        // Add initial balance
        $this->post(route('scrap.add-initial'), [
            'weight' => 20.0
        ]);

        // Write off some scrap
        $this->post(route('scrap.writeoff'), [
            'weight' => 5.0
        ]);

        $this->assertDatabaseCount('scrap_transactions', 2);
        $this->assertDatabaseHas('scrap_inventory', [
            'weight' => 15.0
        ]);
    }

    public function test_scrap_inventory_shows_correct_total()
    {
        $this->actingAs($this->user);

        $this->post(route('scrap.add-initial'), [
            'weight' => 30.0
        ]);

        $this->post(route('scrap.writeoff'), [
            'weight' => 10.0
        ]);

        $response = $this->get(route('scrap.index'));
        $response->assertSee('20.00 kg');
    }
} 
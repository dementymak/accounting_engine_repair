<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WireInventory;
use App\Models\WireTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WireInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_add_new_wire()
    {
        $this->actingAs($this->user);

        $wireData = [
            'diameter' => 0.75,
            'weight' => 15.5
        ];

        $response = $this->post(route('wire-inventory.store'), $wireData);

        $response->assertRedirect(route('wire-inventory.index'));
        $this->assertDatabaseHas('wire_inventory', $wireData);
        $this->assertDatabaseHas('wire_transactions', [
            'type' => 'income',
            'amount' => 15.5
        ]);
    }

    public function test_user_can_add_stock_to_existing_wire()
    {
        $this->actingAs($this->user);

        $wire = WireInventory::factory()->create([
            'diameter' => 0.75,
            'weight' => 10.0
        ]);

        $response = $this->post(route('wire-inventory.add-stock', $wire), [
            'additional_weight' => 5.5
        ]);

        $response->assertRedirect(route('wire-inventory.index'));
        $this->assertDatabaseHas('wire_inventory', [
            'id' => $wire->id,
            'weight' => 15.5
        ]);
        $this->assertDatabaseHas('wire_transactions', [
            'wire_id' => $wire->id,
            'type' => 'income',
            'amount' => 5.5
        ]);
    }

    public function test_user_can_remove_stock_from_wire()
    {
        $this->actingAs($this->user);

        $wire = WireInventory::factory()->create([
            'diameter' => 0.75,
            'weight' => 15.5
        ]);

        $response = $this->post(route('wire-inventory.remove-stock', $wire), [
            'remove_weight' => 5.5
        ]);

        $response->assertRedirect(route('wire-inventory.index'));
        $this->assertDatabaseHas('wire_inventory', [
            'id' => $wire->id,
            'weight' => 10.0
        ]);
        $this->assertDatabaseHas('wire_transactions', [
            'wire_id' => $wire->id,
            'type' => 'expenditure',
            'amount' => -5.5
        ]);
    }

    public function test_cannot_remove_more_stock_than_available()
    {
        $this->actingAs($this->user);

        $wire = WireInventory::factory()->create([
            'diameter' => 0.75,
            'weight' => 5.0
        ]);

        $response = $this->post(route('wire-inventory.remove-stock', $wire), [
            'remove_weight' => 10.0
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('wire_inventory', [
            'id' => $wire->id,
            'weight' => 5.0
        ]);
    }

    public function test_wire_diameter_must_be_unique()
    {
        $this->actingAs($this->user);

        WireInventory::factory()->create([
            'diameter' => 0.75
        ]);

        $response = $this->post(route('wire-inventory.store'), [
            'diameter' => 0.75,
            'weight' => 10.0
        ]);

        $response->assertSessionHasErrors('diameter');
    }

    public function test_user_can_delete_wire_transaction()
    {
        $this->actingAs($this->user);

        $wire = WireInventory::factory()->create([
            'weight' => 15.5
        ]);

        $transaction = WireTransaction::factory()->create([
            'wire_id' => $wire->id,
            'type' => 'income',
            'amount' => 5.5
        ]);

        $response = $this->delete(route('wire-inventory.delete-transaction', $transaction));

        $response->assertRedirect(route('wire-inventory.index'));
        $this->assertDatabaseMissing('wire_transactions', ['id' => $transaction->id]);
        $this->assertDatabaseHas('wire_inventory', [
            'id' => $wire->id,
            'weight' => 10.0
        ]);
    }
} 
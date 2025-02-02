<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WireInventory;
use App\Models\ScrapInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionRollbackTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_wire_transaction_rollback_on_failure()
    {
        $wire = WireInventory::factory()->create([
            'weight' => 20.0
        ]);

        // Start a transaction that should fail
        DB::beginTransaction();
        try {
            // Add stock
            $this->post(route('wire-inventory.add-stock', $wire), [
                'additional_weight' => 10.0
            ]);

            // Simulate a failure
            throw new \Exception('Simulated failure');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // Check that the wire weight remains unchanged
        $this->assertDatabaseHas('wire_inventory', [
            'id' => $wire->id,
            'weight' => 20.0
        ]);
    }

    public function test_scrap_transaction_rollback_on_failure()
    {
        $scrap = ScrapInventory::factory()->create([
            'weight' => 50.0
        ]);

        // Start a transaction that should fail
        DB::beginTransaction();
        try {
            // Write off scrap
            $this->post(route('scrap.writeoff'), [
                'weight' => 20.0
            ]);

            // Simulate a failure
            throw new \Exception('Simulated failure');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // Check that the scrap weight remains unchanged
        $this->assertDatabaseHas('scrap_inventory', [
            'id' => $scrap->id,
            'weight' => 50.0
        ]);
    }

    public function test_repair_card_creation_rollback_on_wire_deduction_failure()
    {
        $wire = WireInventory::factory()->create([
            'weight' => 5.0
        ]);

        // Attempt to create a repair card that would use more wire than available
        $response = $this->post(route('repair-cards.store'), [
            'task_number' => 'TASK-001',
            'repair_card_number' => 'RC-001',
            'wire_id' => $wire->id,
            'total_wire_weight' => 10.0 // More than available
        ]);

        $response->assertSessionHasErrors();

        // Verify no repair card was created
        $this->assertDatabaseMissing('engine_repair_cards', [
            'task_number' => 'TASK-001'
        ]);

        // Verify wire weight remains unchanged
        $this->assertDatabaseHas('wire_inventory', [
            'id' => $wire->id,
            'weight' => 5.0
        ]);
    }

    public function test_multiple_operations_rollback()
    {
        $wire = WireInventory::factory()->create([
            'weight' => 20.0
        ]);
        $scrap = ScrapInventory::factory()->create([
            'weight' => 10.0
        ]);

        // Start a transaction with multiple operations
        DB::beginTransaction();
        try {
            // Add wire stock
            $this->post(route('wire-inventory.add-stock', $wire), [
                'additional_weight' => 5.0
            ]);

            // Add scrap
            $this->post(route('scrap.add-initial'), [
                'weight' => 3.0
            ]);

            // Simulate a failure
            throw new \Exception('Simulated failure');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // Verify all changes were rolled back
        $this->assertDatabaseHas('wire_inventory', [
            'id' => $wire->id,
            'weight' => 20.0
        ]);
        $this->assertDatabaseHas('scrap_inventory', [
            'id' => $scrap->id,
            'weight' => 10.0
        ]);
    }
} 
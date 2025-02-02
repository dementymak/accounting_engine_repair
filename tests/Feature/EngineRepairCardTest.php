<?php

namespace Tests\Feature;

use App\Models\EngineRepairCard;
use App\Models\User;
use App\Models\WireInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class EngineRepairCardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_repair_card()
    {
        $this->actingAs($this->user);

        $repairCardData = [
            'task_number' => 'TASK-001',
            'repair_card_number' => 'RC-001',
            'model' => 'Test Model',
            'temperature_sensor' => 'Sensor Type A',
            'crown_height' => 10.5,
            'connection_type' => 'serial',
            'connection_notes' => 'Test connection notes',
            'groove_distances' => '10/20/30',
            'wires_in_groove' => 5,
            'scrap_weight' => 2.5,
            'winding_resistance' => '0.5 Ohm',
            'mass_resistance' => '1.0 Ohm',
            'notes' => 'Test notes'
        ];

        $response = $this->post(route('repair-cards.store'), $repairCardData);

        $response->assertRedirect();
        $this->assertDatabaseHas('engine_repair_cards', [
            'task_number' => 'TASK-001',
            'repair_card_number' => 'RC-001'
        ]);
    }

    public function test_user_can_update_repair_card()
    {
        $this->actingAs($this->user);

        $repairCard = EngineRepairCard::factory()->create([
            'task_number' => 'TASK-001',
            'repair_card_number' => 'RC-001'
        ]);

        $updatedData = [
            'task_number' => 'TASK-002',
            'repair_card_number' => 'RC-002',
            'model' => 'Updated Model',
            'temperature_sensor' => 'Updated Sensor',
            'crown_height' => 11.5,
            'connection_type' => 'parallel',
            'connection_notes' => 'Updated notes',
            'groove_distances' => '15/25/35',
            'wires_in_groove' => 6,
            'scrap_weight' => 3.5,
            'winding_resistance' => '0.6 Ohm',
            'mass_resistance' => '1.1 Ohm',
            'notes' => 'Updated test notes'
        ];

        $response = $this->put(route('repair-cards.update', $repairCard), $updatedData);

        $response->assertRedirect();

        $this->assertDatabaseHas('engine_repair_cards', [
            'id' => $repairCard->id,
            'task_number' => 'TASK-002',
            'repair_card_number' => 'RC-002'
        ]);
    }

    public function test_user_can_delete_repair_card()
    {
        $this->actingAs($this->user);

        $repairCard = EngineRepairCard::factory()->create();

        $response = $this->delete(route('repair-cards.destroy', $repairCard));

        $response->assertRedirect();

        $this->assertDatabaseMissing('engine_repair_cards', ['id' => $repairCard->id]);
    }

    public function test_user_can_mark_repair_card_as_completed()
    {
        $this->actingAs($this->user);

        $repairCard = EngineRepairCard::factory()->create([
            'completed_at' => null
        ]);

        $response = $this->post(route('repair-cards.toggle-complete', $repairCard));

        $response->assertRedirect();
        
        $updatedCard = EngineRepairCard::find($repairCard->id);
        $this->assertNotNull($updatedCard->completed_at);
    }

    public function test_validation_rules_for_repair_card_creation()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('repair-cards.store'), []);

        $response->assertSessionHasErrors(['task_number', 'repair_card_number']);
    }

    public function test_groove_distances_are_stored_as_array()
    {
        $this->actingAs($this->user);

        $repairCardData = [
            'task_number' => 'TASK-001',
            'repair_card_number' => 'RC-001',
            'groove_distances' => '10/20/30'
        ];

        $this->post(route('repair-cards.store'), $repairCardData);

        $repairCard = EngineRepairCard::first();
        $this->assertIsArray($repairCard->groove_distances);
        $this->assertEquals(['10', '20', '30'], $repairCard->groove_distances);
    }
} 
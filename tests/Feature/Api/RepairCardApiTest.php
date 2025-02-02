<?php

namespace Tests\Feature\Api;

use App\Models\EngineRepairCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RepairCardApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );
    }

    public function test_can_get_repair_cards_list()
    {
        EngineRepairCard::factory()->count(3)->create();

        $response = $this->getJson('/api/repair-cards');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'task_number',
                        'repair_card_number',
                        'model',
                        'created_at'
                    ]
                ]
            ]);
    }

    public function test_can_get_single_repair_card()
    {
        $repairCard = EngineRepairCard::factory()->create();

        $response = $this->getJson("/api/repair-cards/{$repairCard->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $repairCard->id,
                    'task_number' => $repairCard->task_number,
                    'repair_card_number' => $repairCard->repair_card_number
                ]
            ]);
    }

    public function test_can_create_repair_card()
    {
        $repairCardData = [
            'task_number' => 'API-TASK-001',
            'repair_card_number' => 'API-RC-001',
            'model' => 'Test Model',
            'groove_distances' => '10/20/30'
        ];

        $response = $this->postJson('/api/repair-cards', $repairCardData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'task_number' => 'API-TASK-001',
                    'repair_card_number' => 'API-RC-001'
                ]
            ]);

        $this->assertDatabaseHas('engine_repair_cards', [
            'task_number' => 'API-TASK-001'
        ]);
    }

    public function test_can_update_repair_card()
    {
        $repairCard = EngineRepairCard::factory()->create();

        $response = $this->putJson("/api/repair-cards/{$repairCard->id}", [
            'task_number' => 'UPDATED-TASK',
            'repair_card_number' => 'UPDATED-RC'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'task_number' => 'UPDATED-TASK',
                    'repair_card_number' => 'UPDATED-RC'
                ]
            ]);
    }

    public function test_can_delete_repair_card()
    {
        $repairCard = EngineRepairCard::factory()->create();

        $response = $this->deleteJson("/api/repair-cards/{$repairCard->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('engine_repair_cards', [
            'id' => $repairCard->id
        ]);
    }

    public function test_returns_404_for_non_existent_repair_card()
    {
        $response = $this->getJson("/api/repair-cards/999999");
        $response->assertStatus(404);
    }

    public function test_validates_required_fields()
    {
        $response = $this->postJson('/api/repair-cards', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['task_number', 'repair_card_number']);
    }
} 
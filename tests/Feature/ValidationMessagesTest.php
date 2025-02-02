<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationMessagesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_repair_card_validation_messages_in_english()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('repair-cards.store'), [
            'task_number' => '',
            'repair_card_number' => '',
            'crown_height' => 'not-a-number',
            'groove_distances' => 'invalid-format',
            'wires_in_groove' => -1
        ]);

        $response->assertSessionHasErrors([
            'task_number' => 'The Task Number field is required.',
            'repair_card_number' => 'The Repair Card Number field is required.',
            'crown_height' => 'The Crown Height must be a number.',
            'groove_distances' => 'The groove distances format is invalid.',
            'wires_in_groove' => 'The Wires in Groove must be at least 0.'
        ]);
    }

    public function test_repair_card_validation_messages_in_ukrainian()
    {
        $this->actingAs($this->user);
        
        // Switch to Ukrainian
        $this->get(route('language.switch', ['locale' => 'uk']));

        $response = $this->post(route('repair-cards.store'), [
            'task_number' => '',
            'repair_card_number' => '',
            'crown_height' => 'not-a-number',
            'groove_distances' => 'invalid-format',
            'wires_in_groove' => -1
        ]);

        $response->assertSessionHasErrors([
            'task_number' => 'Поле Номер завдання є обов\'язковим для заповнення.',
            'repair_card_number' => 'Поле Номер картки ремонту є обов\'язковим для заповнення.',
            'crown_height' => 'Поле Висота корони повинно містити число.',
            'groove_distances' => 'Неправильний формат відстаней між пазами.',
            'wires_in_groove' => 'Поле Дроти в пазу повинне бути не менше 0.'
        ]);
    }

    public function test_wire_inventory_validation_messages()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('wire-inventory.store'), [
            'diameter' => 'not-a-number',
            'weight' => -1
        ]);

        $response->assertSessionHasErrors([
            'diameter' => 'The diameter must be a number.',
            'weight' => 'The weight must be at least 0.'
        ]);
    }

    public function test_scrap_inventory_validation_messages()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('scrap.add-initial'), [
            'weight' => 'not-a-number'
        ]);

        $response->assertSessionHasErrors([
            'weight' => 'The weight must be a number.'
        ]);
    }

    public function test_unique_validation_messages()
    {
        $this->actingAs($this->user);

        // Create a repair card first
        $this->post(route('repair-cards.store'), [
            'task_number' => 'TASK-001',
            'repair_card_number' => 'RC-001',
            'model' => 'Test Model'
        ]);

        // Try to create another with the same numbers
        $response = $this->post(route('repair-cards.store'), [
            'task_number' => 'TASK-001',
            'repair_card_number' => 'RC-001'
        ]);

        $response->assertSessionHasErrors([
            'task_number' => 'The Task Number has already been taken.',
            'repair_card_number' => 'The Repair Card Number has already been taken.'
        ]);
    }
} 
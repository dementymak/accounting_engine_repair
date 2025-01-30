<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WireUsage extends Model
{
    protected $table = 'wire_usages';

    protected $fillable = [
        'repair_card_id',
        'wire_inventory_id',
        'initial_weight',
        'used_weight',
    ];

    protected $casts = [
        'initial_weight' => 'decimal:2',
        'used_weight' => 'decimal:2',
    ];

    public function repairCard(): BelongsTo
    {
        return $this->belongsTo(EngineRepairCard::class, 'repair_card_id');
    }

    public function wireInventory(): BelongsTo
    {
        return $this->belongsTo(WireInventory::class, 'wire_inventory_id');
    }
} 

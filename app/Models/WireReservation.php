<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WireReservation extends Model
{
    protected $fillable = [
        'wire_inventory_id',
        'repair_card_id',
        'reserved_weight',
        'initial_stock_weight',
    ];

    protected $casts = [
        'reserved_weight' => 'decimal:2',
        'initial_stock_weight' => 'decimal:2',
    ];

    public function wireInventory(): BelongsTo
    {
        return $this->belongsTo(WireInventory::class);
    }

    public function repairCard(): BelongsTo
    {
        return $this->belongsTo(EngineRepairCard::class, 'repair_card_id');
    }
} 
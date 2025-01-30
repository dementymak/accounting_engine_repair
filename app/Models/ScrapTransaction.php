<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapTransaction extends Model
{
    protected $table = 'scrap_transactions';

    protected $fillable = [
        'type',
        'weight',
        'repair_card_id',
        'notes',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function repairCard(): BelongsTo
    {
        return $this->belongsTo(EngineRepairCard::class, 'repair_card_id');
    }
} 
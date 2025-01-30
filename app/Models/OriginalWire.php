<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OriginalWire extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_card_id',
        'diameter',
        'wire_count',
    ];

    protected $casts = [
        'diameter' => 'decimal:2',
        'wire_count' => 'integer',
    ];

    public function repairCard(): BelongsTo
    {
        return $this->belongsTo(EngineRepairCard::class, 'repair_card_id');
    }
} 
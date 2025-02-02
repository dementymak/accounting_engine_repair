@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ isset($repairCard) ? __('messages.edit_repair_card') : __('messages.new_repair_card') }}</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ isset($repairCard) ? route('repair-cards.update', $repairCard) : route('repair-cards.store') }}" 
              method="POST" id="repairCardForm">
            @csrf
            @if(isset($repairCard))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="task_number" class="form-label">{{ __('messages.task_number') }} *</label>
                        <input type="text" class="form-control @error('task_number') is-invalid @enderror" 
                               id="task_number" name="task_number" 
                               value="{{ old('task_number', $repairCard->task_number ?? '') }}" required>
                        @error('task_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="repair_card_number" class="form-label">{{ __('messages.repair_card_number') }}</label>
                        <input type="text" class="form-control @error('repair_card_number') is-invalid @enderror" 
                               id="repair_card_number" name="repair_card_number" 
                               value="{{ old('repair_card_number', $repairCard->repair_card_number ?? '') }}">
                        @error('repair_card_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.crown_height') }} ({{ __('messages.mm') }})</label>
                        <input type="number" step="0.01" name="crown_height" class="form-control"
                               value="{{ old('crown_height', $repairCard->crown_height ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.connection_type') }}</label>
                        <select name="connection_type" class="form-select">
                            <option value="">{{ __('messages.select_type') }}</option>
                            <option value="serial" {{ (old('connection_type', $repairCard->connection_type ?? '') == 'serial') ? 'selected' : '' }}>
                                {{ __('messages.serial') }}
                            </option>
                            <option value="parallel" {{ (old('connection_type', $repairCard->connection_type ?? '') == 'parallel') ? 'selected' : '' }}>
                                {{ __('messages.parallel') }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.connection_notes') }}</label>
                        <textarea name="connection_notes" class="form-control" rows="2">{{ old('connection_notes', $repairCard->connection_notes ?? '') }}</textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.model') }}</label>
                        <input type="text" name="model" class="form-control" maxlength="100"
                               value="{{ old('model', $repairCard->model ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.temperature_sensor') }}</label>
                        <input type="text" name="temperature_sensor" class="form-control"
                               value="{{ old('temperature_sensor', $repairCard->temperature_sensor ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.groove_distances') }} (U)</label>
                        <div class="input-group">
                            <input type="text" name="groove_distances" class="form-control" 
                                   placeholder="{{ __('messages.enter_values_separated_by_slash') }}"
                                   value="{{ old('groove_distances', is_array($repairCard->groove_distances ?? null) ? implode('/', $repairCard->groove_distances) : '') }}">
                            <button type="button" class="btn btn-outline-secondary" id="addGrooveDistance">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.wires_in_groove') }} (N)</label>
                        <input type="number" name="wires_in_groove" class="form-control"
                               value="{{ old('wires_in_groove', $repairCard->wires_in_groove ?? '') }}">
                    </div>
                </div>
            </div>

            <!-- Original Wire Information -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('messages.original_wire_info') }}</h6>
                    <button type="button" class="btn btn-secondary btn-sm" id="addOriginalWire">
                        <i class="fas fa-plus"></i> {{ __('messages.add_original_wire') }}
                    </button>
                </div>
                <div class="card-body">
                    <div id="originalWireContainer">
                        @if(isset($repairCard) && $repairCard->originalWires->count() > 0)
                            @foreach($repairCard->originalWires as $wire)
                                <div class="row original-wire-row mb-3">
                                    <div class="col-md-5">
                                        <input type="text" 
                                               name="original_wires[{{ $loop->index }}][diameter]"
                                               class="form-control original-wire-diameter"
                                               placeholder="{{ __('messages.wire_diameter') }} ({{ __('messages.mm') }})"
                                               value="{{ $wire->diameter }}">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" 
                                               name="original_wires[{{ $loop->index }}][wire_count]"
                                               class="form-control"
                                               placeholder="{{ __('messages.wire_count') }}"
                                               value="{{ $wire->wire_count }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-original-wire">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Wire Usage -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('messages.wire_usage') }}</h6>
                    <div>
                        @if($repairCard && $repairCard->wireUsages->where('completed_at', '!=', null)->count() > 0)
                            <button type="button" class="btn btn-warning btn-sm me-2" id="unlockEditingBtn">
                                <i class="fas fa-unlock"></i> {{ __('messages.unlock_editing') }}
                            </button>
                        @endif
                        <button type="button" class="btn btn-secondary" id="addWireUsage">
                            <i class="fas fa-plus"></i> {{ __('messages.add_wire') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="wireUsageContainer">
                        @if(isset($repairCard) && $repairCard->wireUsages->count() > 0)
                            @foreach($repairCard->wireUsages as $usage)
                                <div class="row wire-usage-row mb-3">
                                    <div class="col-md-3">
                                        <select name="wire_usage[{{ $loop->index }}][wire_inventory_id]" class="form-select wire-select" {{ $usage->isCompleted() ? 'disabled' : '' }}>
                                            <option value="">{{ __('messages.select_wire') }}</option>
                                            @foreach($wires as $wire)
                                                <option value="{{ $wire->id }}" 
                                                        data-weight="{{ $wire->weight }}"
                                                        data-diameter="{{ $wire->diameter }}"
                                                        {{ $usage->wire_inventory_id == $wire->id ? 'selected' : '' }}>
                                                    {{ number_format($wire->diameter, 2) }} {{ __('messages.mm') }} 
                                                    ({{ number_format($wire->weight, 2) }} {{ __('messages.kg') }} {{ __('messages.available') }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($usage->previousRepairCard)
                                            <small class="text-muted">
                                                {{ __('messages.previous_repair_card') }}: {{ $usage->previousRepairCard->repair_card_number }}
                                            </small>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" 
                                               name="wire_usage[{{ $loop->index }}][used_weight]"
                                               class="form-control residual-weight"
                                               placeholder="{{ __('messages.residual_weight') }} ({{ __('messages.kg') }})"
                                               value="{{ $usage->used_weight }}"
                                               data-initial-weight="{{ $usage->initial_weight }}"
                                               {{ $usage->isCompleted() ? 'readonly' : '' }}>
                                        <small class="text-muted">
                                            {{ __('messages.initial_weight') }}: {{ number_format($usage->initial_weight, 2) }} {{ __('messages.kg') }}
                                        </small>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" 
                                               class="form-control consumed-weight"
                                               value="{{ number_format($usage->consumed_weight, 2) }}"
                                               readonly>
                                    </div>
                                    <div class="col-md-2">
                                        @if($usage->completed_at)
                                            <span class="badge bg-success">{{ __('messages.completed') }}</span>
                                        @else
                                            <button type="button" class="btn btn-danger remove-wire">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="mt-3">
                        <strong>{{ __('messages.total_wire_weight') }}: </strong>
                        <span id="totalWireWeight">{{ isset($repairCard) ? number_format($repairCard->total_wire_weight, 2) : '0.00' }}</span> {{ __('messages.kg') }}
                    </div>
                </div>
            </div>

            <!-- Scrap Weight -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('messages.scrap_weight') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.scrap_weight') }} ({{ __('messages.kg') }})</label>
                        <input type="text" name="scrap_weight" class="form-control decimal-input"
                               value="{{ old('scrap_weight', $repairCard->scrap_weight ?? '') }}"
                               placeholder="{{ __('messages.enter_scrap_weight') }}">
                        <small class="form-text text-muted">{{ __('messages.scrap_weight_help') }}</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.winding_resistance') }}</label>
                        <input type="text" name="winding_resistance" class="form-control"
                               value="{{ old('winding_resistance', $repairCard->winding_resistance ?? '') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.mass_resistance') }}</label>
                        <input type="text" name="mass_resistance" class="form-control"
                               value="{{ old('mass_resistance', $repairCard->mass_resistance ?? '') }}">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('messages.notes') }}</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $repairCard->notes ?? '') }}</textarea>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="completionToggle" 
                       name="completed" 
                       data-repair-card-id="{{ $repairCard->id ?? '' }}"
                       {{ isset($repairCard) && $repairCard->completed_at ? 'checked' : '' }}>
                <label class="form-check-label" for="completionToggle">
                    {{ __('messages.mark_completed') }}
                </label>
            </div>

            <div class="mt-4">
                <a href="{{ route('repair-cards.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

@if(config('app.debug'))
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Debug Information</h5>
    </div>
    <div class="card-body">
        <h6>Old Input:</h6>
        <pre>{{ print_r(old(), true) }}</pre>
        
        <h6>Current Repair Card (if editing):</h6>
        <pre>{{ isset($repairCard) ? print_r($repairCard->toArray(), true) : 'New Card' }}</pre>
        
        <h6>Available Wires:</h6>
        <pre>{{ print_r($wires->toArray(), true) }}</pre>
    </div>
</div>
@endif

<!-- Warning Modal -->
<div class="modal fade" id="warningModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.warning') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    {{ __('messages.completed_edit_warning') }}
                </div>
                <p>{{ __('messages.completed_edit_confirmation') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-warning" id="confirmUnlockBtn">{{ __('messages.confirm_unlock') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wire usage management
    const wireUsageContainer = document.getElementById('wireUsageContainer');
    const addWireButton = document.getElementById('addWireUsage');
    const wireUsageTemplate = document.getElementById('wireUsageTemplate');
    let wireUsageCount = document.querySelectorAll('.wire-usage-row').length;

    // Form validation
    const form = document.getElementById('repairCardForm');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const wireRows = document.querySelectorAll('.wire-usage-row');
            let isValid = true;

            wireRows.forEach(row => {
                if (!validateWireUsage(row)) {
                    isValid = false;
                }
            });

            if (isValid) {
                form.submit();
            }
        });
    }

    if (addWireButton && wireUsageTemplate) {
        addWireButton.addEventListener('click', function() {
            const newRow = wireUsageTemplate.content.cloneNode(true);
            // Replace placeholder index with actual count
            newRow.querySelectorAll('[name*="__index__"]').forEach(element => {
                element.name = element.name.replace('__index__', wireUsageCount);
            });
            
            wireUsageContainer.appendChild(newRow);
            wireUsageCount++;
            
            // Initialize new row's functionality
            initializeWireUsageRow(wireUsageContainer.lastElementChild);
            
            if (wireUsageCount >= 10) {
                addWireButton.disabled = true;
            }
        });
    }

    // Initialize existing wire usage rows
    document.querySelectorAll('.wire-usage-row').forEach(initializeWireUsageRow);

    function initializeWireUsageRow(row) {
        const removeButton = row.querySelector('.remove-wire');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                row.remove();
                wireUsageCount--;
                if (addWireButton) {
                    addWireButton.disabled = false;
                }
                updateWireIndexes();
                calculateTotalWeight();
            });
        }

        // Initialize wire select and weight calculation
        const wireSelect = row.querySelector('.wire-select');
        const residualInput = row.querySelector('.residual-weight');
        const consumedInput = row.querySelector('.consumed-weight');
        
        if (wireSelect && residualInput) {
            wireSelect.addEventListener('change', function() {
                updateAvailableWeight(wireSelect, residualInput);
                calculateConsumedWeight(row);
                calculateTotalWeight();
            });
            
            // Add input event for decimal handling
            residualInput.addEventListener('input', function(e) {
                handleDecimalInput(e);
                calculateConsumedWeight(row);
                calculateTotalWeight();
            });
            
            residualInput.addEventListener('blur', function(e) {
                handleDecimalBlur(e);
                calculateConsumedWeight(row);
                calculateTotalWeight();
            });
        }
    }

    function updateAvailableWeight(select, input) {
        const selectedOption = select.selectedOptions[0];
        if (selectedOption) {
            const availableWeight = parseFloat(selectedOption.dataset.weight);
            if (!input.readOnly) {
                input.max = availableWeight;
                input.dataset.initialWeight = availableWeight;
                input.placeholder = `Max: ${availableWeight} ${availableWeight ? 'kg' : ''}`;
            }
        }
    }

    function calculateConsumedWeight(row) {
        const residualInput = row.querySelector('.residual-weight');
        const consumedInput = row.querySelector('.consumed-weight');
        const wireSelect = row.querySelector('.wire-select');
        
        if (residualInput && consumedInput && wireSelect) {
            const selectedOption = wireSelect.selectedOptions[0];
            if (selectedOption && selectedOption.value) {
                const initialWeight = parseFloat(residualInput.dataset.initialWeight) || parseFloat(selectedOption.dataset.weight) || 0;
                const residualWeight = parseFloat(residualInput.value) || 0;
                const consumedWeight = Math.max(0, initialWeight - residualWeight);
                consumedInput.value = consumedWeight.toFixed(2);
            }
        }
    }

    function calculateTotalWeight() {
        let totalWeight = 0;
        document.querySelectorAll('.wire-usage-row').forEach(row => {
            const consumedInput = row.querySelector('.consumed-weight');
            if (consumedInput && consumedInput.value) {
                totalWeight += parseFloat(consumedInput.value) || 0;
            }
        });

        const totalWeightDisplay = document.getElementById('totalWireWeight');
        if (totalWeightDisplay) {
            totalWeightDisplay.textContent = totalWeight.toFixed(2);
        }
    }

    function validateWireUsage(row) {
        const select = row.querySelector('.wire-select');
        const residualInput = row.querySelector('.residual-weight');

        if (!select || !residualInput || residualInput.readOnly) return true;
        
        const selectedOption = select.selectedOptions[0];
        if (!selectedOption || !selectedOption.value) return true;
        
        const availableWeight = parseFloat(selectedOption.dataset.weight) || 0;
        const residualWeight = parseFloat(residualInput.value) || 0;
        
        if (residualWeight > availableWeight) {
            residualInput.setCustomValidity('Недостатньо дроту');
            residualInput.reportValidity();
            return false;
        } else if (residualWeight < 0) {
            residualInput.setCustomValidity('Вага не може бути від\'ємною');
            residualInput.reportValidity();
            return false;
        }
        
        residualInput.setCustomValidity('');
        return true;
    }

    // Helper functions for decimal input handling
    function handleDecimalInput(e) {
        let value = e.target.value.replace(',', '.');
        if (!value) return;
        if (value.endsWith('.')) return;
        if (!/^\d*\.?\d*$/.test(value)) {
            value = value.replace(/[^\d.]/g, '');
        }
        e.target.value = value;
    }

    function handleDecimalBlur(e) {
        let value = e.target.value.replace(',', '.');
        if (value && !value.endsWith('.')) {
            e.target.value = parseFloat(value).toFixed(2);
        }
    }

    // Function to update indexes after removal
    function updateWireIndexes() {
        document.querySelectorAll('.wire-usage-row').forEach((row, index) => {
            row.querySelectorAll('[name*="wire_usage["]').forEach(element => {
                element.name = element.name.replace(/wire_usage\[\d+\]/, `wire_usage[${index}]`);
            });
        });
    }

    // Initialize decimal inputs
    document.querySelectorAll('.decimal-input').forEach(input => {
        input.addEventListener('input', handleDecimalInput);
        input.addEventListener('blur', handleDecimalBlur);
    });

    // Calculate initial total weight
    calculateTotalWeight();

    // Original wire management
    const originalWireContainer = document.getElementById('originalWireContainer');
    const addOriginalWireButton = document.getElementById('addOriginalWire');
    const originalWireTemplate = document.getElementById('originalWireTemplate');
    let originalWireCount = document.querySelectorAll('.original-wire-row').length;

    if (addOriginalWireButton && originalWireTemplate) {
        addOriginalWireButton.addEventListener('click', function() {
            const newRow = originalWireTemplate.content.cloneNode(true);
            // Replace placeholder index with actual count
            newRow.querySelectorAll('[name*="__index__"]').forEach(element => {
                element.name = element.name.replace('__index__', originalWireCount);
            });
            
            originalWireContainer.appendChild(newRow);
            originalWireCount++;
            
            // Initialize new row's functionality
            initializeOriginalWireRow(originalWireContainer.lastElementChild);
            
            if (originalWireCount >= 10) {
                addOriginalWireButton.disabled = true;
            }
        });
    }

    // Helper functions for decimal input handling
    function handleDecimalInput(e) {
        let value = e.target.value.replace(',', '.');
        if (!value) return;
        if (value.endsWith('.')) return;
        if (!/^\d*\.?\d*$/.test(value)) {
            value = value.replace(/[^\d.]/g, '');
        }
        e.target.value = value;
    }

    function handleDecimalBlur(e) {
        let value = e.target.value.replace(',', '.');
        if (value && !value.endsWith('.')) {
            e.target.value = parseFloat(value).toFixed(2);
        }
    }

    // Function to update indexes after removal
    function updateWireIndexes() {
        document.querySelectorAll('.wire-usage-row').forEach((row, index) => {
            row.querySelectorAll('[name*="wire_usage["]').forEach(element => {
                element.name = element.name.replace(/wire_usage\[\d+\]/, `wire_usage[${index}]`);
            });
        });
    }

    // Unlock editing functionality
    const unlockEditingBtn = document.getElementById('unlockEditingBtn');
    const warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
    const confirmUnlockBtn = document.getElementById('confirmUnlockBtn');

    if (unlockEditingBtn && confirmUnlockBtn) {
        unlockEditingBtn.addEventListener('click', function() {
            warningModal.show();
        });

        confirmUnlockBtn.addEventListener('click', function() {
            document.querySelectorAll('.wire-usage-row').forEach(row => {
                const select = row.querySelector('.wire-select');
                const input = row.querySelector('.residual-weight');
                const completedBadge = row.querySelector('.badge.bg-success');
                
                if (select) select.disabled = false;
                if (input) input.readOnly = false;
                
                if (completedBadge) {
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-danger btn-sm remove-wire';
                    removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
                    completedBadge.parentNode.replaceChild(removeBtn, completedBadge);
                }
            });

            if (addWireButton) {
                addWireButton.disabled = false;
            }

            warningModal.hide();
            unlockEditingBtn.disabled = true;
        });
    }

    // Completion toggle functionality
    const completionToggle = document.getElementById('completionToggle');
    if (completionToggle) {
        completionToggle.addEventListener('click', async function(e) {
            e.preventDefault();
            const repairCardId = this.dataset.repairCardId;
            if (!repairCardId) return;

            try {
                completionToggle.disabled = true;
                const response = await fetch(`/repair-cards/${repairCardId}/toggle-complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'An error occurred');
                }

                const alert = createAlert('success', data.message);
                document.querySelector('.container').insertBefore(alert, document.querySelector('.card'));
                
                setTimeout(() => {
                    window.location.href = '/repair-cards';
                }, 1000);
            } catch (error) {
                const alert = createAlert('danger', error.message || 'An error occurred while updating status');
                document.querySelector('.container').insertBefore(alert, document.querySelector('.card'));
                completionToggle.checked = !completionToggle.checked;
            } finally {
                completionToggle.disabled = false;
            }
        });
    }

    // Helper function to create Bootstrap alerts
    function createAlert(type, message) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.role = 'alert';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        return alert;
    }
});
</script>
@endpush

<!-- Wire Usage Template -->
<template id="wireUsageTemplate">
    <div class="row wire-usage-row mb-3">
        <div class="col-md-3">
            <select name="wire_usage[__index__][wire_inventory_id]" class="form-select wire-select" required>
                <option value="">{{ __('messages.select_wire') }}</option>
                @foreach($wires as $wire)
                    <option value="{{ $wire->id }}" 
                            data-weight="{{ $wire->weight }}"
                            data-diameter="{{ $wire->diameter }}">
                        {{ number_format($wire->diameter, 2) }} {{ __('messages.mm') }}
                        ({{ number_format($wire->weight, 2) }} {{ __('messages.kg') }} {{ __('messages.available') }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" 
                   name="wire_usage[__index__][used_weight]"
                   class="form-control residual-weight"
                   placeholder="{{ __('messages.residual_weight') }} ({{ __('messages.kg') }})"
                   required>
        </div>
        <div class="col-md-3">
            <input type="text" 
                   class="form-control consumed-weight"
                   readonly>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-wire">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>

<!-- Original Wire Template -->
<template id="originalWireTemplate">
    <div class="row original-wire-row mb-3">
        <div class="col-md-5">
            <input type="text" 
                   name="original_wires[__index__][diameter]"
                   class="form-control original-wire-diameter"
                   placeholder="{{ __('messages.wire_diameter') }} ({{ __('messages.mm') }})">
        </div>
        <div class="col-md-5">
            <input type="number" 
                   name="original_wires[__index__][wire_count]"
                   class="form-control"
                   placeholder="{{ __('messages.wire_count') }}">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-original-wire">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>






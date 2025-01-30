@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ isset($repairCard) ? __('messages.edit_repair_card') . ' #' . $repairCard->number : __('messages.new_repair_card') }}</h5>
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
                        <label class="form-label">{{ __('messages.number') }} *</label>
                        <input type="number" name="number" class="form-control" required
                               value="{{ old('number', $repairCard->number ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.task_number') }}</label>
                        <input type="text" name="task_number" class="form-control"
                               value="{{ old('task_number', $repairCard->task_number ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.repair_card_number') }}</label>
                        <input type="text" name="repair_card_number" class="form-control"
                               value="{{ old('repair_card_number', $repairCard->repair_card_number ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.crown_height') }} (mm)</label>
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
                                        <input type="number" step="0.01" 
                                               name="original_wires[{{ $loop->index }}][diameter]"
                                               class="form-control original-wire-diameter"
                                               placeholder="{{ __('messages.wire_diameter') }} (mm)"
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
                    <button type="button" class="btn btn-secondary" id="addWireUsage">
                        <i class="fas fa-plus"></i> {{ __('messages.add_wire') }}
                    </button>
                </div>
                <div class="card-body">
                    <div id="wireUsageContainer">
                        @if(isset($repairCard) && $repairCard->wireUsages->count() > 0)
                            @foreach($repairCard->wireUsages as $usage)
                                <div class="row wire-usage-row mb-3">
                                    <div class="col-md-3">
                                        <select name="wire_usage[{{ $loop->index }}][wire_inventory_id]" class="form-select wire-select">
                                            <option value="">{{ __('messages.select_wire') }}</option>
                                            @foreach($wires as $wire)
                                                <option value="{{ $wire->id }}" 
                                                        data-weight="{{ $wire->weight }}"
                                                        data-diameter="{{ $wire->diameter }}"
                                                        {{ $usage->wire_inventory_id == $wire->id ? 'selected' : '' }}>
                                                    {{ number_format($wire->diameter, 2) }}mm ({{ number_format($wire->weight, 2) }}kg {{ __('messages.available') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" step="0.01" 
                                               name="wire_usage[{{ $loop->index }}][used_weight]"
                                               class="form-control residual-weight"
                                               placeholder="{{ __('messages.residual_weight') }} (kg)"
                                               value="{{ $usage->used_weight }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" step="0.01" 
                                               class="form-control consumed-weight"
                                               placeholder="{{ __('messages.consumed_weight') }} (kg)"
                                               readonly
                                               value="{{ $usage->initial_weight - $usage->used_weight }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-wire">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="mt-3">
                        <strong>{{ __('messages.total_wire_weight') }}: </strong>
                        <span id="totalWireWeight">{{ isset($repairCard) ? number_format($repairCard->calculateTotalUsedWeight(), 2) : '0.00' }}</span> kg
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
                        <label class="form-label">{{ __('messages.scrap_weight') }} (kg)</label>
                        <input type="number" step="0.01" min="0" name="scrap_weight" class="form-control"
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

            @if(isset($repairCard))
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="completed" class="form-check-input" id="completed"
                           {{ $repairCard->completed_at ? 'checked' : '' }}>
                    <label class="form-check-label" for="completed">{{ __('messages.mark_completed') }}</label>
                </div>
            </div>
            @endif

            <div class="text-end">
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Add form submission debugging
    $('#repairCardForm').on('submit', function(e) {
        console.log('Form data:', $(this).serializeArray());
    });

    // Original Wire Template
    let originalWireTemplate = `
        <div class="row original-wire-row mb-3">
            <div class="col-md-5">
                <input type="number" step="0.01" 
                       name="original_wires[INDEX][diameter]"
                       class="form-control original-wire-diameter"
                       placeholder="{{ __('messages.wire_diameter') }} (mm)">
            </div>
            <div class="col-md-5">
                <input type="number" 
                       name="original_wires[INDEX][wire_count]"
                       class="form-control"
                       placeholder="{{ __('messages.wire_count') }}">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-original-wire">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    // Wire Usage Template
    let wireUsageTemplate = `
        <div class="row wire-usage-row mb-3">
            <div class="col-md-3">
                <select name="wire_usage[INDEX][wire_inventory_id]" class="form-select wire-select">
                    <option value="">{{ __('messages.select_wire') }}</option>
                    @foreach($wires as $wire)
                        <option value="{{ $wire->id }}" 
                                data-weight="{{ $wire->weight }}"
                                data-diameter="{{ $wire->diameter }}">
                            {{ number_format($wire->diameter, 2) }}mm ({{ number_format($wire->weight, 2) }}kg {{ __('messages.available') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" 
                       name="wire_usage[INDEX][used_weight]"
                       class="form-control residual-weight"
                       placeholder="{{ __('messages.residual_weight') }} (kg)">
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" 
                       class="form-control consumed-weight"
                       placeholder="{{ __('messages.consumed_weight') }} (kg)"
                       readonly>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-wire">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    // Add Original Wire
    $('#addOriginalWire').click(function() {
        let index = $('.original-wire-row').length;
        if (index < 10) {
            let newRow = originalWireTemplate.replace(/INDEX/g, index);
            $('#originalWireContainer').append(newRow);
            updateOriginalWireIndexes();
        } else {
            alert('{{ __('messages.max_original_wires') }}');
        }
    });

    // Remove Original Wire
    $(document).on('click', '.remove-original-wire', function() {
        $(this).closest('.original-wire-row').remove();
        updateOriginalWireIndexes();
    });

    // Add Wire Usage
    $('#addWireUsage').click(function() {
        let index = $('.wire-usage-row').length;
        let newRow = wireUsageTemplate.replace(/INDEX/g, index);
        $('#wireUsageContainer').append(newRow);
        updateWireIndexes();
    });

    // Remove Wire Usage
    $(document).on('click', '.remove-wire', function() {
        $(this).closest('.wire-usage-row').remove();
        updateWireIndexes();
        calculateTotalWeight();
    });

    // Update indexes for original wires
    function updateOriginalWireIndexes() {
        $('.original-wire-row').each(function(index) {
            $(this).find('input').each(function() {
                let name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                }
            });
        });
    }

    // Update indexes for wire usage
    function updateWireIndexes() {
        $('.wire-usage-row').each(function(index) {
            $(this).find('select, input').each(function() {
                let name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                }
            });
        });
    }

    // Calculate consumed weight and update total
    $(document).on('change', '.wire-select, .residual-weight', function() {
        let row = $(this).closest('.wire-usage-row');
        calculateConsumedWeight(row);
        calculateTotalWeight();
    });

    function calculateConsumedWeight(row) {
        let maxWeight = row.find('.wire-select option:selected').data('weight') || 0;
        let residualWeight = parseFloat(row.find('.residual-weight').val()) || 0;
        let consumedWeight = Math.max(0, maxWeight - residualWeight);
        row.find('.consumed-weight').val(consumedWeight.toFixed(2));
    }

    function calculateTotalWeight() {
        let total = 0;
        $('.consumed-weight').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        $('#totalWireWeight').text(total.toFixed(2));
    }

    // Show wire recommendations based on original wire
    $(document).on('change', '.original-wire-diameter', function() {
        let diameter = parseFloat($(this).val());
        if (diameter) {
            $('.wire-select option').each(function() {
                let wireDiameter = $(this).data('diameter');
                if (wireDiameter === diameter) {
                    $(this).addClass('text-success fw-bold');
                } else {
                    $(this).removeClass('text-success fw-bold');
                }
            });
        }
    });
});
</script>
@endpush 





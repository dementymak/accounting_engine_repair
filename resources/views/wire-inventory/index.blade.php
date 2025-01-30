@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('messages.add_new_wire') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('wire-inventory.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.wire_diameter') }} (mm)</label>
                        <input type="number" step="0.01" name="diameter" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.initial_weight') }} (kg)</label>
                        <input type="number" step="0.01" name="weight" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('messages.add_wire') }}</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('messages.wire_inventory') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('messages.wire_diameter') }}</th>
                                <th>{{ __('messages.weight') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wires as $wire)
                            <tr>
                                <td>{{ number_format($wire->diameter, 2) }} mm</td>
                                <td>{{ number_format($wire->weight, 2) }} kg</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#addStockModal"
                                            data-wire-id="{{ $wire->id }}"
                                            data-wire-diameter="{{ $wire->diameter }}">
                                        <i class="fas fa-plus"></i> {{ __('messages.add_stock') }}
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Wire Transactions History -->
                <h5 class="mt-4">{{ __('messages.wire_transactions') }}</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('messages.wire_diameter') }}</th>
                                <th>{{ __('messages.type') }}</th>
                                <th>{{ __('messages.amount') }}</th>
                                <th>{{ __('messages.repair_card') }}</th>
                                <th>{{ __('messages.notes') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wireTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>{{ number_format($transaction->wire->diameter, 2) }} mm</td>
                                <td>
                                    @if($transaction->type === 'income')
                                        <span class="text-success">{{ __('messages.income') }}</span>
                                    @else
                                        <span class="text-danger">{{ __('messages.expenditure') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->type === 'income')
                                        <span class="text-success">+{{ number_format($transaction->amount, 2) }} kg</span>
                                    @else
                                        <span class="text-danger">{{ number_format($transaction->amount, 2) }} kg</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->repair_card)
                                        #{{ $transaction->repair_card->repair_card_number }}
                                    @endif
                                </td>
                                <td>{{ $transaction->notes }}</td>
                                <td>
                                    <form action="{{ route('wire-inventory.delete-transaction', $transaction) }}" 
                                          method="POST" style="display: inline-block;"
                                          onsubmit="return confirm('{{ __('messages.confirm_delete_transaction') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $wireTransactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.add_wire_stock') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addStockForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>{{ __('messages.adding_stock_for_wire') }}: <span id="addStockDiameter"></span>mm</p>
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.additional_weight') }} (kg)</label>
                        <input type="number" step="0.01" name="additional_weight" class="form-control" required min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('messages.add_stock') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove Stock Modal -->
<div class="modal fade" id="removeStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.remove_wire_stock') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="removeStockForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>{{ __('messages.removing_stock_for_wire') }}: <span id="removeStockDiameter"></span>mm</p>
                    <p>{{ __('messages.available_weight') }}: <span id="removeStockAvailable"></span>kg</p>
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.weight_to_remove') }} (kg)</label>
                        <input type="number" step="0.01" name="remove_weight" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning">{{ __('messages.remove_stock') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Stock Modal Handling
    const addStockModal = document.getElementById('addStockModal');
    if (addStockModal) {
        addStockModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const wireId = button.getAttribute('data-wire-id');
            const wireDiameter = button.getAttribute('data-wire-diameter');
            
            // Update the modal content
            document.getElementById('addStockDiameter').textContent = wireDiameter;
            
            // Update the form action URL
            const form = document.getElementById('addStockForm');
            form.action = `/wire-inventory/${wireId}/add-stock`;
        });
    }

    // Wire Usage Calculation
    function updateWireUsageCalculations() {
        const wireRows = document.querySelectorAll('.wire-usage-row');
        let totalWeight = 0;

        wireRows.forEach(row => {
            const select = row.querySelector('.wire-select');
            const residualInput = row.querySelector('.residual-weight');
            const consumedInput = row.querySelector('.consumed-weight');

            if (select && residualInput && consumedInput) {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption) {
                    const availableWeight = parseFloat(selectedOption.getAttribute('data-weight')) || 0;
                    const residualWeight = parseFloat(residualInput.value) || 0;
                    const consumedWeight = Math.max(0, availableWeight - residualWeight);
                    
                    consumedInput.value = consumedWeight.toFixed(2);
                    totalWeight += consumedWeight;
                }
            }
        });

        // Update total weight display
        const totalWeightDisplay = document.getElementById('totalWireWeight');
        if (totalWeightDisplay) {
            totalWeightDisplay.textContent = totalWeight.toFixed(2);
        }
    }

    // Add event listeners for wire usage changes
    document.addEventListener('change', function(event) {
        if (event.target.classList.contains('wire-select') || 
            event.target.classList.contains('residual-weight')) {
            updateWireUsageCalculations();
        }
    });

    // Initial calculation
    updateWireUsageCalculations();

    // Add Wire Usage Row
    const addWireButton = document.getElementById('addWire');
    if (addWireButton) {
        addWireButton.addEventListener('click', function() {
            const container = document.getElementById('wireUsageContainer');
            const rowCount = container.querySelectorAll('.wire-usage-row').length;
            
            const newRow = document.createElement('div');
            newRow.className = 'wire-usage-row mb-3';
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <select name="wire_usage[${rowCount}][wire_id]" class="form-select wire-select" required>
                            <option value="">{{ __('messages.select_wire') }}</option>
                            @foreach($wires as $wire)
                            <option value="{{ $wire->id }}" 
                                    data-weight="{{ $wire->weight }}"
                                    data-diameter="{{ $wire->diameter }}">
                                {{ number_format($wire->diameter, 2) }}mm ({{ number_format($wire->weight, 2) }}kg)
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" 
                               name="wire_usage[${rowCount}][residual_weight]"
                               class="form-control residual-weight"
                               placeholder="{{ __('messages.residual_weight') }}"
                               required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" 
                               class="form-control consumed-weight"
                               readonly>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-wire">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(newRow);
            updateWireUsageCalculations();
        });
    }

    // Remove Wire Usage Row
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-wire') || 
            event.target.closest('.remove-wire')) {
            const row = event.target.closest('.wire-usage-row');
            if (row) {
                row.remove();
                updateWireUsageCalculations();
            }
        }
    });
});
</script>
@endpush 




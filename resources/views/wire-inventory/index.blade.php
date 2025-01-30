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
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('messages.diameter') }} (mm)</th>
                                <th>{{ __('messages.available_weight') }} (kg)</th>
                                <th class="text-end">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wires as $wire)
                            <tr>
                                <td>{{ number_format($wire->diameter, 2) }}</td>
                                <td>{{ number_format($wire->weight, 2) }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-success"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#addStockModal"
                                            data-wire-id="{{ $wire->id }}"
                                            data-wire-diameter="{{ $wire->diameter }}">
                                        <i class="fas fa-plus"></i> {{ __('messages.add_stock') }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#removeStockModal"
                                            data-wire-id="{{ $wire->id }}"
                                            data-wire-diameter="{{ $wire->diameter }}"
                                            data-wire-weight="{{ $wire->weight }}">
                                        <i class="fas fa-minus"></i> {{ __('messages.remove') }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">{{ __('messages.no_wires') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Wire History Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('messages.wire_history') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('messages.date_time') }}</th>
                                <th>{{ __('messages.wire_diameter') }} (mm)</th>
                                <th>{{ __('messages.transaction_type') }}</th>
                                <th>{{ __('messages.amount') }} (kg)</th>
                                <th>{{ __('messages.repair_card_number') }}</th>
                                <th>{{ __('messages.notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wireTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ number_format($transaction->wire->diameter, 2) }}</td>
                                <td>
                                    @if($transaction->type === 'income')
                                        <span class="badge bg-success">{{ __('messages.income') }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ __('messages.expenditure') }}</span>
                                    @endif
                                </td>
                                <td>{{ number_format(abs($transaction->amount), 2) }}</td>
                                <td>
                                    @if($transaction->repair_card_id)
                                        <a href="{{ route('repair-cards.show', $transaction->repair_card_id) }}">
                                            #{{ $transaction->repair_card->number }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $transaction->notes ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('messages.no_transactions') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($wireTransactions->hasPages())
                    <div class="mt-4">
                        {{ $wireTransactions->links() }}
                    </div>
                @endif
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
                        <input type="number" step="0.01" name="additional_weight" class="form-control" required>
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
$(document).ready(function() {
    $('#addStockModal').on('show.bs.modal', function(event) {
        let button = $(event.relatedTarget);
        let wireId = button.data('wire-id');
        let wireDiameter = button.data('wire-diameter');
        
        $('#addStockDiameter').text(wireDiameter.toFixed(2));
        $('#addStockForm').attr('action', `/wire-inventory/${wireId}/add-stock`);
    });

    $('#removeStockModal').on('show.bs.modal', function(event) {
        let button = $(event.relatedTarget);
        let wireId = button.data('wire-id');
        let wireDiameter = button.data('wire-diameter');
        let wireWeight = button.data('wire-weight');
        
        $('#removeStockDiameter').text(wireDiameter.toFixed(2));
        $('#removeStockAvailable').text(wireWeight.toFixed(2));
        $('#removeStockForm').attr('action', `/wire-inventory/${wireId}/remove-stock`);
        $('input[name="remove_weight"]').attr('max', wireWeight);
    });
});
</script>
@endpush 



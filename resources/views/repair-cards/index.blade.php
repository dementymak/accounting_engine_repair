@extends('layouts.app')

@section('content')
<style>
.wire-usage-details {
    font-size: 0.9em;
}

.wire-usage-details div {
    margin-bottom: 2px;
    color: #666;
}

.wire-usage-details strong {
    display: block;
    margin-top: 4px;
    color: #333;
    border-top: 1px solid #eee;
    padding-top: 4px;
}
</style>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('messages.repair_cards') }}</h5>
        <a href="{{ route('repair-cards.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.new_repair_card') }}
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ __('messages.task_number') }}</th>
                        <th>{{ __('messages.repair_card_number') }}</th>
                        <th>{{ __('messages.created') }}</th>
                        <th>{{ __('messages.completed') }}</th>
                        <th>{{ __('messages.model') }}</th>
                        <th>{{ __('messages.connection') }}</th>
                        <th>{{ __('messages.total_wire_weight') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th class="text-end">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repairCards as $card)
                    <tr>
                        <td>{{ $card->task_number ?? '-' }}</td>
                        <td>{{ $card->repair_card_number ?? '-' }}</td>
                        <td>{{ $card->created_at->format('Y-m-d') }}</td>
                        <td>{{ $card->completed_at ? $card->completed_at->format('Y-m-d') : '-' }}</td>
                        <td>{{ $card->model ?? '-' }}</td>
                        <td>{{ ucfirst($card->connection_type ?? '-') }}</td>
                        <td>
                            @if($card->wireUsages->count() > 0)
                                <div class="wire-usage-details">
                                    @foreach($card->wire_usage_summary as $usage)
                                        <div>
                                            {{ number_format($usage['diameter'], 2) }} {{ __('messages.mm') }}:
                                            {{ number_format($usage['consumed_weight'], 2) }} {{ __('messages.kg') }}
                                        </div>
                                    @endforeach
                                    <strong>{{ __('messages.total_wire_weight') }}: {{ number_format($card->total_wire_weight, 2) }} {{ __('messages.kg') }}</strong>
                                </div>
                            @else
                                0.00 {{ __('messages.kg') }}
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $card->completed_at ? 'bg-success' : 'bg-warning' }}">
                                {{ $card->completed_at ? __('messages.completed') : __('messages.in_progress') }}
                            </span>
                        </td>
                        <td class="text-end table-actions">
                            <a href="{{ route('repair-cards.edit', $card) }}" class="btn btn-sm btn-primary btn-icon">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('repair-cards.destroy', $card) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger btn-icon" 
                                        onclick="return confirm('{{ __('messages.confirm_delete_repair_card') }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">{{ __('messages.no_repair_cards') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Add sorting functionality here if needed
    $('.table th').click(function() {
        // Implementation for sorting
    });
});
</script>
@endpush 
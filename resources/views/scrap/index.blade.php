@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.scrap_inventory_management') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('messages.current_scrap_weight') }}</h6>
                                    <h3>{{ $scrapInventory ? number_format($scrapInventory->weight, 2) : '0.00' }} kg</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('messages.add_initial_balance') }}</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('scrap.add-initial') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="weight" class="form-label">{{ __('messages.weight') }} (kg)</label>
                                            <input type="number" step="0.01" min="0" class="form-control" id="weight" name="weight" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">{{ __('messages.add_initial_balance') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('messages.write_off_scrap') }}</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('scrap.writeoff') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="weight" class="form-label">{{ __('messages.weight_to_remove') }} (kg)</label>
                                            <input type="number" step="0.01" min="0" class="form-control" id="weight" name="weight" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-danger">{{ __('messages.write_off_scrap') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">{{ __('messages.transaction_history') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.date') }}</th>
                                            <th>{{ __('messages.type') }}</th>
                                            <th>{{ __('messages.weight') }}</th>
                                            <th>{{ __('messages.repair_card') }}</th>
                                            <th>{{ __('messages.notes') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td>
                                                @if($transaction->type === 'initial')
                                                    <span class="badge bg-primary">{{ __('messages.initial_balance') }}</span>
                                                @elseif($transaction->type === 'repair_card')
                                                    <span class="badge bg-success">{{ __('messages.from_repair_card') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('messages.write_off') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($transaction->weight, 2) }} kg</td>
                                            <td>
                                                @if($transaction->repair_card_id)
                                                    <a href="{{ route('repair-cards.edit', $transaction->repair_card_id) }}">
                                                        #{{ $transaction->repairCard->repair_card_number }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $transaction->notes }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
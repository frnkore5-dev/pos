@extends('layouts.app')

@section('title', __('sale::messages.cash_register_close_report'))

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.home') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cash-register-sessions.index') }}">{{ __('sale::messages.cash_register_history') }}</a></li>
        <li class="breadcrumb-item active">#{{ $session->id }}</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center">
                        <div>
                            {{ __('sale::messages.cash_register_close_report') }} <strong>#{{ $session->id }}</strong>
                        </div>
                        <a target="_blank" class="btn btn-sm btn-success mfs-auto mfe-1 d-print-none" href="{{ route('app.pos.cash-register.pdf', $session->id) }}">
                            <i class="bi bi-file-earmark-pdf"></i> {{ __('sale::messages.print_report') }}
                        </a>
                        <button onclick="window.print()" class="btn btn-sm btn-secondary mfe-1 d-print-none">
                            <i class="bi bi-printer"></i> {{ __('sale::messages.print') }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <h5 class="mb-2 border-bottom pb-2">{{ __('sale::messages.cashier') }}</h5>
                                <div><strong>{{ $session->user->name }}</strong></div>
                                <div>{{ $session->user->email }}</div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <h5 class="mb-2 border-bottom pb-2">{{ __('sale::messages.opened_at') }}</h5>
                                <div>{{ $session->opened_at->format('d/m/Y H:i') }}</div>
                                @if($session->opening_note)
                                    <div class="mt-2 small text-muted">{{ $session->opening_note }}</div>
                                @endif
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <h5 class="mb-2 border-bottom pb-2">{{ __('sale::messages.closed_at') }}</h5>
                                <div>{{ $session->closed_at->format('d/m/Y H:i') }}</div>
                                @if($session->closing_note)
                                    <div class="mt-2 small text-muted">{{ $session->closing_note }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <h5 class="mb-3">{{ __('sale::messages.close_summary_title') }}</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <th>{{ __('sale::messages.opening_balance') }}</th>
                                                <td class="text-right">{{ format_currency($summary['opening_cents'] / 100) }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('sale::messages.total_cash') }}</th>
                                                <td class="text-right text-success font-weight-bold">{{ format_currency($summary['cash_cents'] / 100) }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('sale::messages.total_card') }}</th>
                                                <td class="text-right text-primary font-weight-bold">{{ format_currency($summary['card_cents'] / 100) }}</td>
                                            </tr>
                                            @if($summary['other_cents'] > 0)
                                            <tr>
                                                <th>{{ __('sale::messages.other_payments') }}</th>
                                                <td class="text-right">{{ format_currency($summary['other_cents'] / 100) }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <th>{{ __('sale::messages.pending_amount') }}</th>
                                                <td class="text-right text-danger font-weight-bold">{{ format_currency($summary['pending_cents'] / 100) }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('sale::messages.sales_count') }}</th>
                                                <td class="text-right">{{ $summary['sales_count'] }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('sale::messages.total_sales') }}</th>
                                                <td class="text-right">{{ format_currency($summary['total_sales_cents'] / 100) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-4">
                                <h5 class="mb-3">{{ __('sale::messages.cash_register_close_title') }}</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr class="table-warning">
                                                <th>{{ __('sale::messages.expected_cash_in_drawer') }}</th>
                                                <td class="text-right font-weight-bold">{{ format_currency($session->expected_cash_amount) }}</td>
                                            </tr>
                                            <tr class="table-warning">
                                                <th>{{ __('sale::messages.counted_cash') }}</th>
                                                <td class="text-right font-weight-bold">{{ format_currency($session->closing_amount_counted) }}</td>
                                            </tr>
                                            <tr class="table-warning">
                                                <th>{{ __('sale::messages.over_short') }}</th>
                                                <td class="text-right font-weight-bold @if($session->cash_difference > 0) text-success @elseif($session->cash_difference < 0) text-danger @else text-muted @endif">
                                                    {{ format_currency($session->cash_difference) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3">{{ __('sale::messages.session_sales') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                <tr class="text-center">
                                    <th>{{ __('sale::messages.reference') }}</th>
                                    <th>{{ __('sale::messages.customer') }}</th>
                                    <th>{{ __('sale::messages.total_amount') }}</th>
                                    <th>{{ __('sale::messages.paid_amount') }}</th>
                                    <th>{{ __('sale::messages.due_amount') }}</th>
                                    <th>{{ __('sale::messages.payment_method') }}</th>
                                    <th>{{ __('sale::messages.payment_status') }}</th>
                                    <th class="d-print-none">{{ __('sale::messages.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($session->sales as $sale)
                                    <tr class="text-center">
                                        <td>{{ $sale->reference }}</td>
                                        <td>{{ $sale->customer_name }}</td>
                                        <td>{{ format_currency($sale->total_amount) }}</td>
                                        <td>{{ format_currency($sale->paid_amount) }}</td>
                                        <td>{{ format_currency($sale->due_amount) }}</td>
                                        <td>{{ $sale->payment_method }}</td>
                                        <td>
                                            @include('sale::partials.payment-status', ['data' => $sale])
                                        </td>
                                        <td class="d-print-none">
                                            @can('show_sales')
                                                <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">{{ __('sale::messages.no_session_sales') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

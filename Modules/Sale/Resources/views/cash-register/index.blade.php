@extends('layouts.app')

@section('title', __('sale::messages.cash_register_history'))

@section('third_party_stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.home') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">{{ __('sale::messages.sales') }}</a></li>
        <li class="breadcrumb-item active">{{ __('sale::messages.cash_register_history') }}</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-1">{{ __('sale::messages.cash_register_history') }}</h5>
                        <p class="text-muted small mb-3">{{ __('sale::messages.cash_register_history_help') }}</p>

                        <form id="cash-register-filters" class="mb-4" onsubmit="return false;">
                            <div class="form-row align-items-end">
                                <div class="col-md-3">
                                    <div class="form-group mb-md-0">
                                        <label for="filter_start_date">{{ __('sale::messages.filter_start_date') }}</label>
                                        <input type="date" class="form-control" id="filter_start_date"
                                               value="{{ now()->subDays(30)->format('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-md-0">
                                        <label for="filter_end_date">{{ __('sale::messages.filter_end_date') }}</label>
                                        <input type="date" class="form-control" id="filter_end_date"
                                               value="{{ now()->format('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-md-0">
                                        <label for="filter_user_id">{{ __('sale::messages.cashier') }}</label>
                                        <select class="form-control" id="filter_user_id">
                                            <option value="">{{ __('sale::messages.all_cashiers') }}</option>
                                            @foreach($cashiers as $cashier)
                                                <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-md-0">
                                        <button type="button" class="btn btn-primary btn-block" id="apply-filters">
                                            <i class="bi bi-funnel"></i> {{ __('sale::messages.apply_filters') }}
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-block mt-2" id="reset-filters">
                                            <i class="bi bi-x-circle"></i> {{ __('sale::messages.clear_filters') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            {!! $dataTable->table() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
    {!! $dataTable->scripts() !!}
    <script>
        $(document).ready(function () {
            var tableName = 'cash-register-sessions-table';

            function reloadTable() {
                if (window.LaravelDataTables && window.LaravelDataTables[tableName]) {
                    window.LaravelDataTables[tableName].ajax.reload();
                }
            }

            $('#apply-filters').on('click', reloadTable);
            $('#filter_user_id').on('change', reloadTable);

            $('#reset-filters').on('click', function () {
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                $('#filter_user_id').val('');
                reloadTable();
            });

            $('#filter_start_date, #filter_end_date').on('change', function () {
                if ($('#filter_start_date').val() && $('#filter_end_date').val()) {
                    reloadTable();
                }
            });
        });
    </script>
@endpush

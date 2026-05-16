@extends('layouts.app')

@section('title', 'POS')

@section('third_party_stylesheets')

@endsection

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.home') }}</a></li>
        <li class="breadcrumb-item active">POS</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @include('utils.alerts')
            </div>
            @if($cashRegisterSession)
                <div class="col-12 mb-3">
                    <div class="alert alert-info mb-0 d-flex flex-wrap justify-content-between align-items-center">
                        <div class="mb-2 mb-md-0">
                            <strong>{{ __('sale::messages.cash_register_open_banner') }}</strong>
                            · {{ __('sale::messages.opening_balance') }}:
                            {{ format_currency($cashRegisterSession->opening_amount) }}
                            · {{ __('sale::messages.opened_at') }}:
                            {{ $cashRegisterSession->opened_at->format('d/m/Y H:i') }}
                        </div>
                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#cashRegisterCloseModal">
                            <i class="bi bi-door-closed"></i> {{ __('sale::messages.close_cash_register') }}
                        </button>
                    </div>
                </div>
            @endif
            <div class="col-lg-7">
                <livewire:search-product/>
                <livewire:pos.product-list :categories="$product_categories"/>
            </div>
            <div class="col-lg-5">
                <livewire:pos.checkout :cart-instance="'sale'" :customers="$customers"/>
            </div>
        </div>
    </div>

    @include('sale::pos.partials.cash-register')
@endsection

@push('page_scripts')
    <script src="{{ asset('js/jquery-mask-money.js') }}"></script>
    <script>
        $(document).ready(function () {
            var moneyOpts = {
                prefix: '{{ settings()->currency->symbol }}',
                thousands: '{{ settings()->currency->thousand_separator }}',
                decimal: '{{ settings()->currency->decimal_separator }}',
                allowZero: true,
            };

            @if(!$cashRegisterSession)
            $('#cashRegisterOpenModal').modal({backdrop: 'static', keyboard: false});
            $('#cashRegisterOpenModal').modal('show');
            @endif

            $('#opening_amount').maskMoney(moneyOpts);
            $('#opening_amount').maskMoney('mask');

            $('#cash-register-open-form').on('submit', function () {
                var opening = $('#opening_amount').maskMoney('unmasked')[0];
                $('#opening_amount').val(opening);
            });

            $('#cashRegisterCloseModal').on('show.bs.modal', function () {
                $('#cash-register-summary-loading').show();
                $('#cash-register-expected-display').text('—');
                fetch(@json(route('app.pos.cash-register.summary')), {headers: {'Accept': 'application/json'}})
                    .then(function (r) {
                        if (!r.ok) throw new Error('summary');
                        return r.json();
                    })
                    .then(function (data) {
                        $('#cash-register-expected-display').text(data.expected_formatted);
                    })
                    .catch(function () {
                        $('#cash-register-expected-display').text('{{ __('sale::messages.summary_load_error') }}');
                    })
                    .finally(function () {
                        $('#cash-register-summary-loading').hide();
                    });
            });

            $('#closing_amount_counted').maskMoney(moneyOpts);

            $('#cashRegisterCloseModal').on('shown.bs.modal', function () {
                $('#closing_amount_counted').maskMoney('mask');
            });

            $('#cash-register-close-form').on('submit', function () {
                var counted = $('#closing_amount_counted').maskMoney('unmasked')[0];
                $('#closing_amount_counted').val(counted);
            });

            window.addEventListener('showCheckoutModal', event => {
                $('#checkoutModal').modal('show');

                $('#paid_amount').maskMoney({
                    prefix:'{{ settings()->currency->symbol }}',
                    thousands:'{{ settings()->currency->thousand_separator }}',
                    decimal:'{{ settings()->currency->decimal_separator }}',
                    allowZero: false,
                });

                $('#total_amount').maskMoney({
                    prefix:'{{ settings()->currency->symbol }}',
                    thousands:'{{ settings()->currency->thousand_separator }}',
                    decimal:'{{ settings()->currency->decimal_separator }}',
                    allowZero: true,
                });

                $('#paid_amount').maskMoney('mask');
                $('#total_amount').maskMoney('mask');

                $('#checkout-form').submit(function () {
                    var paid_amount = $('#paid_amount').maskMoney('unmasked')[0];
                    $('#paid_amount').val(paid_amount);
                    var total_amount = $('#total_amount').maskMoney('unmasked')[0];
                    $('#total_amount').val(total_amount);
                });
            });
        });
    </script>

@endpush

@extends('layouts.app')

@section('title', 'POS')

@section('third_party_stylesheets')
    @include('sale::pos.partials.styles')
@endsection

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.home') }}</a></li>
        <li class="breadcrumb-item active">POS</li>
    </ol>
@endsection

@section('content')
    <div id="pos-app" class="pos-app container-fluid">
        <div class="pos-toolbar d-flex justify-content-end align-items-center mb-2">
            <button type="button" id="pos-dark-toggle" class="btn btn-sm btn-outline-secondary" title="{{ __('sale::messages.toggle_dark_mode') }}">
                <i class="bi bi-moon-stars" id="pos-dark-icon"></i>
                <span id="pos-dark-label" class="d-none d-md-inline ml-1">{{ __('sale::messages.dark_mode') }}</span>
            </button>
        </div>
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
                <livewire:search-product :pos-mode="true"/>
                <livewire:pos.product-list :categories="$product_categories"/>
            </div>
            <div class="col-lg-5">
                <livewire:pos.checkout :cart-instance="'sale'" :customers="$customers" :default-customer-id="$defaultCustomerId"/>
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

            var cashRegisterExpectedAmount = 0;

            function formatMoneyAmount(amount) {
                var negative = amount < 0;
                var value = Math.abs(amount).toFixed(2).split('.');
                value[0] = value[0].replace(/\B(?=(\d{3})+(?!\d))/g, moneyOpts.thousands);
                return (negative ? '-' : '') + moneyOpts.prefix + value.join(moneyOpts.decimal);
            }

            function updateCashDifference() {
                var counted = parseFloat($('#closing_amount_counted').maskMoney('unmasked')[0]) || 0;
                var diff = counted - cashRegisterExpectedAmount;

                if ($('#closing_amount_counted').val() === '') {
                    $('#cr-difference-box').hide();
                    return;
                }

                $('#cr-difference-box').show();
                $('#cr-difference-display').text(formatMoneyAmount(diff));

                $('#cr-difference-display').removeClass('text-success text-danger text-muted');
                if (diff > 0) {
                    $('#cr-difference-display').addClass('text-success');
                } else if (diff < 0) {
                    $('#cr-difference-display').addClass('text-danger');
                } else {
                    $('#cr-difference-display').addClass('text-muted');
                }
            }

            @if(session('cash_register_closed_id'))
            window.open(@json(route('app.pos.cash-register.pdf', session('cash_register_closed_id'))), '_blank');
            @endif

            @if(session('pos_sale_completed_id'))
            window.open(@json(route('sales.pdf', session('pos_sale_completed_id'))), '_blank');
            @endif

            function focusPosProductSearch() {
                var input = document.getElementById('pos-product-search');
                if (input && !document.querySelector('.modal.show')) {
                    input.focus();
                    input.select();
                }
            }

            document.addEventListener('livewire:initialized', function () {
                Livewire.on('focus-product-search', function () {
                    setTimeout(focusPosProductSearch, 50);
                });

                Livewire.on('posProductAdded', function () {
                    playPosBeep();
                });

                Livewire.on('posProductNotFound', function () {
                    playPosErrorBeep();
                });

                Livewire.on('posProductOutOfStock', function () {
                    playPosWarningBeep();
                });

                bindPosBarcodeScanner();
            });

            document.addEventListener('livewire:navigated', bindPosBarcodeScanner);

            if (window.Livewire) {
                Livewire.hook('morph.updated', function () {
                    bindPosBarcodeScanner();
                });
            }

            function bindPosBarcodeScanner() {
                var input = document.getElementById('pos-product-search');
                if (!input || input.dataset.posScanBound === '1') {
                    return;
                }
                input.dataset.posScanBound = '1';

                var scanState = {
                    firstKeyAt: 0,
                    lastKeyAt: 0,
                    keyCount: 0,
                    autoScanTimer: null,
                };

                function getSearchComponent() {
                    var host = input.closest('[wire\\:id]');
                    if (!host) {
                        return null;
                    }
                    return Livewire.find(host.getAttribute('wire:id'));
                }

                function submitScan() {
                    var code = input.value.trim();
                    if (!code) {
                        return;
                    }
                    var component = getSearchComponent();
                    if (component) {
                        component.call('scanProduct', code);
                    }
                    scanState.keyCount = 0;
                    scanState.firstKeyAt = 0;
                }

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        clearTimeout(scanState.autoScanTimer);
                        submitScan();
                        return;
                    }

                    var now = Date.now();
                    if (scanState.keyCount === 0) {
                        scanState.firstKeyAt = now;
                    }
                    scanState.lastKeyAt = now;
                    scanState.keyCount++;
                });

                input.addEventListener('input', function () {
                    clearTimeout(scanState.autoScanTimer);
                    scanState.autoScanTimer = setTimeout(function () {
                        var code = input.value.trim();
                        if (code.length < 3 || scanState.keyCount < 3) {
                            scanState.keyCount = 0;
                            return;
                        }

                        var duration = scanState.lastKeyAt - scanState.firstKeyAt;
                        // Escaneo: muchos caracteres en muy poco tiempo (lector de barras)
                        if (duration < 300) {
                            submitScan();
                        }

                        scanState.keyCount = 0;
                        scanState.firstKeyAt = 0;
                    }, 120);
                });
            }

            function playPosBeep() {
                playPosTone(920, 0.12, 0.12);
            }

            function playPosErrorBeep() {
                playPosTone(320, 0.14, 0.18);
                setTimeout(function () {
                    playPosTone(240, 0.14, 0.22);
                }, 160);
            }

            function playPosWarningBeep() {
                playPosTone(520, 0.1, 0.15);
                setTimeout(function () {
                    playPosTone(420, 0.1, 0.18);
                }, 140);
            }

            function playPosTone(frequency, volume, duration) {
                try {
                    var ctx = new (window.AudioContext || window.webkitAudioContext)();
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'square';
                    osc.frequency.value = frequency;
                    gain.gain.setValueAtTime(volume, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + duration);
                } catch (e) {
                    //
                }
            }

            (function initPosDarkMode() {
                var root = document.getElementById('pos-app');
                var toggle = document.getElementById('pos-dark-toggle');
                var icon = document.getElementById('pos-dark-icon');
                var label = document.getElementById('pos-dark-label');
                var storageKey = 'posDarkMode';

                function applyDark(enabled) {
                    if (!root) return;
                    root.classList.toggle('pos-dark', enabled);
                    if (icon) {
                        icon.className = enabled ? 'bi bi-sun' : 'bi bi-moon-stars';
                    }
                    if (label) {
                        label.textContent = enabled
                            ? @json(__('sale::messages.light_mode'))
                            : @json(__('sale::messages.dark_mode'));
                    }
                }

                applyDark(localStorage.getItem(storageKey) === '1');

                if (toggle) {
                    toggle.addEventListener('click', function () {
                        var enabled = !root.classList.contains('pos-dark');
                        localStorage.setItem(storageKey, enabled ? '1' : '0');
                        applyDark(enabled);
                    });
                }
            })();

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

            $('#cashRegisterOpenModal').on('hidden.bs.modal', function () {
                setTimeout(focusPosProductSearch, 150);
            });

            $('#checkoutModal').on('hidden.bs.modal', function () {
                setTimeout(focusPosProductSearch, 150);
            });

            @if($cashRegisterSession)
            setTimeout(focusPosProductSearch, 300);
            @endif

            $('#cashRegisterCloseModal').on('show.bs.modal', function () {
                $('#cash-register-summary-loading').show();
                $('#cash-register-expected-display, #cr-opening, #cr-total-cash, #cr-total-card, #cr-total-other, #cr-total-pending, #cr-sales-count, #cr-total-sales').text('—');
                $('#cr-other-row').hide();
                $('#cr-difference-box').hide();
                $('#closing_amount_counted').val('');
                cashRegisterExpectedAmount = 0;

                fetch(@json(route('app.pos.cash-register.summary')), {headers: {'Accept': 'application/json'}})
                    .then(function (r) {
                        if (!r.ok) throw new Error('summary');
                        return r.json();
                    })
                    .then(function (data) {
                        cashRegisterExpectedAmount = data.expected_cents / 100;
                        $('#cr-opening').text(data.opening_formatted);
                        $('#cr-total-cash').text(data.cash_formatted);
                        $('#cr-total-card').text(data.card_formatted);
                        $('#cr-total-pending').text(data.pending_formatted);
                        $('#cr-sales-count').text(data.sales_count);
                        $('#cr-total-sales').text(data.total_sales_formatted);
                        $('#cash-register-expected-display').text(data.expected_formatted);

                        if (data.other_cents > 0) {
                            $('#cr-other-row').show();
                            $('#cr-total-other').text(data.other_formatted);
                        }
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
                $('#closing_amount_counted').trigger('focus');
            });

            $('#closing_amount_counted').on('keyup change blur', updateCashDifference);

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

                updatePosChangeDisplay();

                $('#checkout-form').submit(function () {
                    var paid_amount = $('#paid_amount').maskMoney('unmasked')[0];
                    $('#paid_amount').val(paid_amount);
                    var total_amount = $('#total_amount').maskMoney('unmasked')[0];
                    $('#total_amount').val(total_amount);
                });
            });

            function getCheckoutTotalAmount() {
                return parseFloat($('#total_amount').maskMoney('unmasked')[0]) || 0;
            }

            function setCheckoutPaidAmount(amount) {
                $('#paid_amount').maskMoney('mask', amount);
                updatePosChangeDisplay();
            }

            function updatePosChangeDisplay() {
                var total = getCheckoutTotalAmount();
                var paid = parseFloat($('#paid_amount').maskMoney('unmasked')[0]) || 0;
                var change = paid - total;

                if (paid <= 0 || change < 0) {
                    $('#pos-change-display').addClass('d-none');
                    return;
                }

                $('#pos-change-amount').text(formatMoneyAmount(change));
                $('#pos-change-display').removeClass('d-none');
            }

            $(document).on('click', '.pos-quick-pay', function () {
                var total = getCheckoutTotalAmount();
                var amount = $(this).data('amount');

                if (amount === 'exact') {
                    setCheckoutPaidAmount(total);
                } else {
                    var bill = parseFloat(amount);
                    var paid = bill;

                    while (paid < total) {
                        paid += bill;
                    }

                    setCheckoutPaidAmount(paid);
                }

                $('#paid_amount').trigger('focus');
            });

            $(document).on('keyup change blur', '#paid_amount', updatePosChangeDisplay);

            document.addEventListener('keydown', function (e) {
                var tag = (e.target.tagName || '').toLowerCase();
                var isTyping = tag === 'input' || tag === 'textarea' || tag === 'select';
                var isProductSearch = e.target.id === 'pos-product-search';

                if (e.key === 'F2') {
                    e.preventDefault();
                    Livewire.dispatch('posProceedCheckout');
                    return;
                }

                if (e.key === 'F4') {
                    e.preventDefault();
                    if (confirm(@json(__('sale::messages.confirm_clear_cart')))) {
                        Livewire.dispatch('posResetCart');
                    }
                    return;
                }

                if (e.key === 'Escape') {
                    $('.modal.show').modal('hide');
                    return;
                }

                if (isTyping && !isProductSearch) {
                    return;
                }

                if (e.key === '+' || e.key === '=') {
                    e.preventDefault();
                    Livewire.dispatch('posIncrementLastItem');
                }

                if (e.key === '-') {
                    e.preventDefault();
                    Livewire.dispatch('posDecrementLastItem');
                }
            });
        });
    </script>

@endpush

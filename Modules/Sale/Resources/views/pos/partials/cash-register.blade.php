{{-- Apertura de caja (obligatoria si no hay sesión abierta) --}}
<div class="modal fade" id="cashRegisterOpenModal" tabindex="-1" role="dialog" aria-labelledby="cashRegisterOpenModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cashRegisterOpenModalLabel">
                    <i class="bi bi-cash-stack text-primary"></i> {{ __('sale::messages.cash_register_open_title') }}
                </h5>
                @if($cashRegisterSession)
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('sale::messages.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                @endif
            </div>
            <form id="cash-register-open-form" method="POST" action="{{ route('app.pos.cash-register.open') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">{{ __('sale::messages.cash_register_open_help') }}</p>
                    <div class="form-group">
                        <label for="opening_amount">{{ __('sale::messages.opening_balance') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="opening_amount" id="opening_amount" value="0" required autocomplete="off">
                    </div>
                    <div class="form-group mb-0">
                        <label for="opening_note">{{ __('sale::messages.opening_note') }}</label>
                        <textarea class="form-control" name="opening_note" id="opening_note" rows="3" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    @if($cashRegisterSession)
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('sale::messages.close') }}</button>
                    @endif
                    <button type="submit" class="btn btn-primary">{{ __('sale::messages.confirm_open_register') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Cierre de caja --}}
<div class="modal fade" id="cashRegisterCloseModal" tabindex="-1" role="dialog" aria-labelledby="cashRegisterCloseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cashRegisterCloseModalLabel">
                    <i class="bi bi-cash text-warning"></i> {{ __('sale::messages.cash_register_close_title') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('sale::messages.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="cash-register-close-form" method="POST" action="{{ route('app.pos.cash-register.close') }}">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted mb-2" id="cash-register-summary-loading" style="display:none;">
                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                        {{ __('sale::messages.loading') }}
                    </p>

                    <h6 class="text-muted text-uppercase small mb-2">{{ __('sale::messages.close_summary_title') }}</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th class="bg-light">{{ __('sale::messages.opening_balance') }}</th>
                                    <td class="text-right font-weight-bold" id="cr-opening">—</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">{{ __('sale::messages.total_cash') }}</th>
                                    <td class="text-right font-weight-bold text-success" id="cr-total-cash">—</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">{{ __('sale::messages.total_card') }}</th>
                                    <td class="text-right font-weight-bold text-primary" id="cr-total-card">—</td>
                                </tr>
                                <tr id="cr-other-row" style="display:none;">
                                    <th class="bg-light">{{ __('sale::messages.other_payments') }}</th>
                                    <td class="text-right font-weight-bold" id="cr-total-other">—</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">{{ __('sale::messages.pending_amount') }}</th>
                                    <td class="text-right font-weight-bold text-danger" id="cr-total-pending">—</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">{{ __('sale::messages.sales_count') }}</th>
                                    <td class="text-right" id="cr-sales-count">—</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">{{ __('sale::messages.total_sales') }}</th>
                                    <td class="text-right" id="cr-total-sales">—</td>
                                </tr>
                                <tr class="table-warning">
                                    <th>{{ __('sale::messages.expected_cash_in_drawer') }}</th>
                                    <td class="text-right font-weight-bold lead mb-0" id="cash-register-expected-display">—</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="closing_amount_counted">{{ __('sale::messages.counted_cash') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" name="closing_amount_counted" id="closing_amount_counted" required autocomplete="off">
                        <small class="form-text text-muted">{{ __('sale::messages.counted_cash_help') }}</small>
                    </div>

                    <div class="alert alert-secondary py-2 mb-3" id="cr-difference-box" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ __('sale::messages.cash_difference') }}:</span>
                            <strong class="lead mb-0" id="cr-difference-display">—</strong>
                        </div>
                        <small class="text-muted d-block mt-1">{{ __('sale::messages.cash_difference_help') }}</small>
                    </div>

                    <div class="form-group mb-0">
                        <label for="closing_note">{{ __('sale::messages.closing_note') }}</label>
                        <textarea class="form-control" name="closing_note" id="closing_note" rows="3" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('sale::messages.close') }}</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-printer"></i> {{ __('sale::messages.close_and_print') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

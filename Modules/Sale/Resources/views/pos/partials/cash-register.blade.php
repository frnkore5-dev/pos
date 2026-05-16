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
    <div class="modal-dialog" role="document">
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
                    <p class="mb-2">
                        <span class="text-muted">{{ __('sale::messages.expected_cash_in_drawer') }}</span><br>
                        <strong class="lead" id="cash-register-expected-display">—</strong>
                    </p>
                    <p class="small text-muted mb-3" id="cash-register-summary-loading" style="display:none;">{{ __('sale::messages.loading') }}</p>
                    <div class="form-group">
                        <label for="closing_amount_counted">{{ __('sale::messages.counted_cash') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="closing_amount_counted" id="closing_amount_counted" required autocomplete="off">
                    </div>
                    <div class="form-group mb-0">
                        <label for="closing_note">{{ __('sale::messages.closing_note') }}</label>
                        <textarea class="form-control" name="closing_note" id="closing_note" rows="3" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('sale::messages.close') }}</button>
                    <button type="submit" class="btn btn-warning">{{ __('sale::messages.confirm_close_register') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

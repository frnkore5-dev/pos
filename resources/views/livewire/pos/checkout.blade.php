<div>
    <div class="card border-0 shadow-sm pos-checkout-card">
        <div class="card-body">
            <div>
                @if (session()->has('message'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <div class="alert-body">
                            <span>{{ session('message') }}</span>
                            <button type="button" class="close" data-dismiss="alert" aria-label="{{ __('sale::messages.close') }}">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label for="customer_id">{{ __('sale::messages.customer') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        @can('create_customers')
                        <div class="input-group-prepend">
                            <button type="button" wire:click="openCustomerModal" class="btn btn-primary" title="{{ __('sale::messages.quick_customer') }}">
                                <i class="bi bi-person-plus"></i>
                            </button>
                        </div>
                        @endcan
                        <select wire:model.live="customer_id" id="customer_id" class="form-control">
                            <option value="" selected>{{ __('sale::messages.select_customer') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr class="text-center">
                            <th class="align-middle">{{ __('sale::messages.product') }}</th>
                            <th class="align-middle">{{ __('sale::messages.price') }}</th>
                            <th class="align-middle">{{ __('sale::messages.quantity') }}</th>
                            <th class="align-middle">{{ __('sale::messages.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($cart_items->isNotEmpty())
                            @foreach($cart_items as $cart_item)
                                <tr>
                                    <td class="align-middle">
                                        {{ $cart_item->name }} <br>
                                        <span class="badge badge-success">
                                        {{ $cart_item->options->code }}
                                    </span>
                                        @include('livewire.includes.product-cart-modal')
                                    </td>

                                    <td class="align-middle">
                                        {{ format_currency($cart_item->price) }}
                                    </td>

                                    <td class="align-middle">
                                        @include('livewire.includes.product-cart-quantity')
                                    </td>

                                    <td class="align-middle text-center">
                                        <a href="#" wire:click.prevent="removeItem('{{ $cart_item->rowId }}')">
                                            <i class="bi bi-x-circle font-2xl text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center">
                        <span class="text-danger">
                            {{ __('sale::messages.select_products_message') }}
                        </span>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tr>
                                <th>{{ __('sale::messages.order_tax') }} ({{ $global_tax }}%)</th>
                                <td>(+) {{ format_currency(Cart::instance($cart_instance)->tax()) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('sale::messages.discount') }} ({{ $global_discount }}%)</th>
                                <td>(-) {{ format_currency(Cart::instance($cart_instance)->discount()) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('sale::messages.shipping') }}</th>
                                <input type="hidden" value="{{ $shipping }}" name="shipping_amount">
                                <td>(+) {{ format_currency($shipping) }}</td>
                            </tr>
                            <tr class="text-primary">
                                <th>{{ __('sale::messages.grand_total') }}</th>
                                @php
                                    $total_with_shipping = Cart::instance($cart_instance)->total() + (float) $shipping
                                @endphp
                                <th>
                                    (=) {{ format_currency($total_with_shipping) }}
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="tax_percentage">{{ __('sale::messages.order_tax') }} (%)</label>
                        <input wire:model.blur="global_tax" type="number" class="form-control" min="0" max="100" value="{{ $global_tax }}" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="discount_percentage">{{ __('sale::messages.discount') }} (%)</label>
                        <input wire:model.blur="global_discount" type="number" class="form-control" min="0" max="100" value="{{ $global_discount }}" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="shipping_amount">{{ __('sale::messages.shipping') }}</label>
                        <input wire:model.blur="shipping" type="number" class="form-control" min="0" value="0" required step="0.01">
                    </div>
                </div>
            </div>

            @if(!empty($heldSales))
                <div class="mb-3">
                    <label class="d-block small text-muted mb-1">{{ __('sale::messages.held_sales') }}</label>
                    <div class="list-group list-group-flush border rounded">
                        @foreach($heldSales as $held)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-2" wire:key="held-{{ $held['id'] }}">
                                <div class="small">
                                    <strong>{{ $held['customer_name'] }}</strong>
                                    · {{ format_currency($held['total']) }}
                                    · {{ $held['held_at'] }}
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" wire:click="resumeHeld('{{ $held['id'] }}')" class="btn btn-outline-primary">
                                        <i class="bi bi-play-fill"></i> {{ __('sale::messages.resume_sale') }}
                                    </button>
                                    <button type="button" wire:click="deleteHeld('{{ $held['id'] }}')" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="form-group d-flex justify-content-center flex-wrap mb-2">
                <button wire:click="holdSale" type="button" class="btn btn-pill btn-warning mr-2 mb-2" {{ $total_amount == 0 ? 'disabled' : '' }}>
                    <i class="bi bi-pause-fill"></i> {{ __('sale::messages.hold_sale') }}
                </button>
            </div>

            <div class="form-group d-flex justify-content-center flex-wrap mb-0">
                <button wire:click="resetCart" type="button" class="btn btn-pill btn-danger mr-3 mb-2" id="pos-reset-cart-btn"><i class="bi bi-x"></i> {{ __('sale::messages.reset') }} <small class="d-none d-md-inline">(F4)</small></button>
                <button wire:loading.attr="disabled" wire:click="proceed" type="button" class="btn btn-pill btn-primary mb-2" id="pos-proceed-btn" {{  $total_amount == 0 ? 'disabled' : '' }}><i class="bi bi-check"></i> {{ __('sale::messages.proceed') }} <small class="d-none d-md-inline">(F2)</small></button>
            </div>
            <p class="text-center text-muted small mb-0 mt-2">{{ __('sale::messages.keyboard_shortcuts_hint') }}</p>
        </div>
    </div>

    {{--Checkout Modal--}}
    @include('livewire.pos.includes.checkout-modal')

    {{--Quick Customer Modal--}}
    @if($showCustomerModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-person-plus"></i> {{ __('sale::messages.quick_customer') }}</h5>
                        <button type="button" class="close" wire:click="closeCustomerModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="customer_search">{{ __('sale::messages.search_customer') }}</label>
                            <input wire:model.live.debounce.300ms="customerSearch" type="text" id="customer_search" class="form-control" placeholder="{{ __('sale::messages.search_customer_placeholder') }}">
                        </div>
                        @if($customers->isNotEmpty() && $customerSearch !== '')
                            <div class="list-group mb-3" style="max-height: 120px; overflow-y: auto;">
                                @foreach($customers as $customer)
                                    <button type="button" wire:click="selectCustomerFromModal({{ $customer->id }})" class="list-group-item list-group-item-action py-1">
                                        {{ $customer->customer_name }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        <hr>
                        <p class="small text-muted mb-2">{{ __('sale::messages.quick_customer_help') }}</p>
                        <div class="form-group">
                            <label for="new_customer_name">{{ __('sale::messages.customer_name') }} <span class="text-danger">*</span></label>
                            <input wire:model="new_customer_name" type="text" id="new_customer_name" class="form-control" autofocus>
                            @error('new_customer_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="new_customer_phone">{{ __('sale::messages.phone') }}</label>
                            <input wire:model="new_customer_phone" type="text" id="new_customer_phone" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCustomerModal">{{ __('sale::messages.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="quickCreateCustomer">{{ __('sale::messages.create_customer') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>


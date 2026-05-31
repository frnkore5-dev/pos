<div class="position-relative" @if($posMode) wire:init="initPosFocus" @endif>
    <div class="card mb-0 border-0 shadow-sm pos-search-card">
        <div class="card-body py-2">
            <div class="form-group mb-0">
                <div class="input-group input-group-lg">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="bi bi-{{ $posMode ? 'upc-scan' : 'search' }} text-primary"></i>
                        </div>
                    </div>
                    <input
                        id="pos-product-search"
                        wire:keydown.escape="resetQuery"
                        @if($posMode)
                            wire:model.live="query"
                        @else
                            wire:keydown.enter.prevent="scanProduct"
                            wire:model.live.debounce.500ms="query"
                        @endif
                        type="text"
                        class="form-control pos-search-input"
                        autocomplete="off"
                        placeholder="{{ $posMode ? __('product::messages.scan_placeholder') : __('product::messages.search_placeholder') }}"
                        @if($posMode) autofocus @endif
                    >
                </div>
                @if($posMode)
                    <small class="form-text text-muted mb-0 mt-1">{{ __('product::messages.scan_help') }}</small>
                @endif
            </div>
        </div>
    </div>

    @if($posMode && $scanNotFoundCode)
        <div class="modal fade show d-block pos-scan-modal" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.45);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white py-2">
                        <h6 class="modal-title mb-0">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{ __('sale::messages.scan_product_not_found_title') }}
                        </h6>
                        <button type="button" class="close text-white" wire:click="dismissScanAlerts">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">{{ __('sale::messages.scan_product_not_found_message') }}</p>
                        <div class="alert alert-light border mb-0 py-2">
                            <strong>{{ __('product::messages.code') }}:</strong>
                            <code class="ml-1">{{ $scanNotFoundCode }}</code>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="dismissScanAlerts">
                            {{ __('sale::messages.close') }}
                        </button>
                        @can('create_products')
                            <a href="{{ route('products.create', ['product_code' => $scanNotFoundCode, 'return_to' => 'pos']) }}"
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> {{ __('sale::messages.create_product_from_scan') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($posMode && $outOfStockProduct)
        <div class="modal fade show d-block pos-scan-modal" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.45);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-warning py-2">
                        <h6 class="modal-title mb-0">
                            <i class="bi bi-box-seam"></i> {{ __('sale::messages.scan_out_of_stock_title') }}
                        </h6>
                        <button type="button" class="close" wire:click="dismissScanAlerts">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">{{ __('sale::messages.scan_out_of_stock_message') }}</p>
                        <ul class="list-unstyled mb-0 small">
                            <li><strong>{{ __('sale::messages.product') }}:</strong> {{ $outOfStockProduct['product_name'] ?? '—' }}</li>
                            <li><strong>{{ __('product::messages.code') }}:</strong> {{ $outOfStockProduct['product_code'] ?? '—' }}</li>
                            <li class="text-danger font-weight-bold">
                                <strong>{{ __('sale::messages.stock') }}:</strong> {{ (int) ($outOfStockProduct['product_quantity'] ?? 0) }}
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="dismissScanAlerts">
                            {{ __('sale::messages.cancel') }}
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" wire:click="confirmAddOutOfStock">
                            <i class="bi bi-cart-plus"></i> {{ __('sale::messages.add_anyway') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!$posMode)
        <div wire:loading.delay.shortest class="card position-absolute mt-1 border-0" style="z-index: 1;left: 0;right: 0;">
            <div class="card-body shadow py-2">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="sr-only">{{ __('product::messages.loading') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($query))
            <div wire:click="resetQuery" class="position-fixed w-100 h-100" style="left: 0; top: 0; right: 0; bottom: 0;z-index: 1;"></div>
            @if($search_results->isNotEmpty())
                <div class="card position-absolute mt-1" style="z-index: 2;left: 0;right: 0;border: 0;">
                    <div class="card-body shadow">
                        <ul class="list-group list-group-flush">
                            @foreach($search_results as $result)
                                <li class="list-group-item list-group-item-action">
                                    <a wire:click.prevent="selectProduct({{ $result->id }})" href="#">
                                        {{ $result->product_name }} | {{ $result->product_code }}
                                    </a>
                                </li>
                            @endforeach
                            @if($search_results->count() >= $how_many)
                                 <li class="list-group-item list-group-item-action text-center">
                                     <a wire:click.prevent="loadMore" class="btn btn-primary btn-sm" href="#">
                                         {{ __('product::messages.load_more') }} <i class="bi bi-arrow-down-circle"></i>
                                     </a>
                                 </li>
                            @endif
                        </ul>
                    </div>
                </div>
            @else
                <div class="card position-absolute mt-1 border-0" style="z-index: 1;left: 0;right: 0;">
                    <div class="card-body shadow">
                        <div class="alert alert-warning mb-0">
                            {{ __('product::messages.no_product_found') }}
                        </div>
                    </div>
                </div>
            @endif
        @endif
    @endif
</div>

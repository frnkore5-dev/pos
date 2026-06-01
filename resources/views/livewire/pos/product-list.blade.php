<div>
    <div class="card border-0 shadow-sm mt-2 pos-product-grid">
        <div class="card-body pb-2">
            <livewire:pos.filter :categories="$categories"/>

            @if(!$showGrid)
                <div class="pos-idle-state text-center py-4 px-3">
                    <i class="bi bi-upc-scan pos-idle-icon mb-3 d-block"></i>
                    <h6 class="mb-2">{{ __('sale::messages.pos_idle_title') }}</h6>
                    <p class="text-muted small mb-3">{{ __('sale::messages.pos_idle_help') }}</p>
                    <button type="button" wire:click="browseAll" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-grid-3x3-gap"></i> {{ __('sale::messages.browse_catalog') }}
                    </button>
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">
                        @if($browseAllActive)
                            {{ __('sale::messages.showing_full_catalog') }}
                        @elseif($searchQuery)
                            {{ __('sale::messages.showing_search_results') }}
                        @elseif($category_id)
                            {{ __('sale::messages.showing_category') }}
                        @endif
                    </small>
                    <button type="button" wire:click="hideGrid" class="btn btn-sm btn-link text-muted p-0">
                        <i class="bi bi-x-lg"></i> {{ __('sale::messages.hide_catalog') }}
                    </button>
                </div>
                <div class="row position-relative">
                    <div wire:loading.flex class="col-12 position-absolute justify-content-center align-items-center pos-grid-loader">
                        <div class="spinner-border text-primary spinner-border-sm" role="status">
                            <span class="sr-only">{{ __('sale::messages.loading') }}</span>
                        </div>
                    </div>
                    @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        @forelse($products as $product)
                            <div wire:click.prevent="selectProduct({{ $product->id }})" class="col-6 col-md-4 col-lg-3 col-xl-2 mb-3" style="cursor: pointer;">
                                <div @class(['card pos-product-card h-100 shadow-sm', 'is-out-of-stock' => $product->product_quantity <= 0])>
                                    <div class="pos-product-image-wrap">
                                        <img src="{{ $product->getProductImageUrl() }}" alt="{{ $product->product_name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $product->productImagePlaceholder() }}';">
                                        <span @class([
                                            'pos-product-stock badge',
                                            'badge-success' => $product->product_quantity > 5,
                                            'badge-warning' => $product->product_quantity > 0 && $product->product_quantity <= 5,
                                            'badge-danger' => $product->product_quantity <= 0,
                                        ]) title="{{ __('sale::messages.stock') }}: {{ $product->product_quantity }}">
                                            {{ $product->product_quantity }}
                                        </span>
                                    </div>
                                    <div class="pos-product-body">
                                        <h6 class="pos-product-name" title="{{ $product->product_name }}">{{ $product->product_name }}</h6>
                                        <div class="pos-product-meta">
                                            <span class="pos-product-code" title="{{ $product->product_code }}">{{ $product->product_code }}</span>
                                            <span class="pos-product-price">{{ format_currency($product->product_price) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">
                                    {{ __('sale::messages.products_not_found') }}
                                </div>
                            </div>
                        @endforelse
                    @endif
                </div>
                @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
                    <div class="mt-1">
                        {{ $products->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

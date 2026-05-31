<div>
    <div class="pos-filter-bar mb-2">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <span class="small text-muted font-weight-bold">{{ __('sale::messages.product_category') }}</span>
            <div class="d-flex align-items-center">
                <select wire:model.live="showCount" class="form-control form-control-sm pos-count-select mr-2" style="width: auto;">
                    <option value="12">12</option>
                    <option value="18">18</option>
                    <option value="24">24</option>
                    <option value="30">30</option>
                    <option value="">{{ __('sale::messages.all_products') }}</option>
                </select>
                <button type="button" wire:click="browseAllProducts" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-grid-3x3-gap"></i> {{ __('sale::messages.browse_catalog') }}
                </button>
            </div>
        </div>
        <div class="pos-category-chips d-flex flex-wrap">
            <button type="button"
                    wire:click="selectCategory('')"
                    @class(['btn btn-sm pos-category-chip mr-1 mb-1', 'btn-primary' => $category === '', 'btn-outline-secondary' => $category !== ''])>
                {{ __('sale::messages.all_products') }}
            </button>
            @foreach($categories as $cat)
                <button type="button"
                        wire:click="selectCategory('{{ $cat->id }}')"
                        @class(['btn btn-sm pos-category-chip mr-1 mb-1', 'btn-primary' => (string) $category === (string) $cat->id, 'btn-outline-secondary' => (string) $category !== (string) $cat->id])>
                    {{ $cat->category_name }}
                </button>
            @endforeach
        </div>
    </div>
</div>

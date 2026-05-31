<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;
use Modules\Product\Entities\Product;

class SearchProduct extends Component
{
    public $query;
    public $search_results;
    public $how_many;
    public $posMode = false;
    public $scanNotFoundCode = null;
    public $outOfStockProduct = null;

    public function mount($posMode = false)
    {
        $this->posMode = (bool) $posMode;
        $this->query = '';
        $this->how_many = 5;
        $this->search_results = Collection::empty();
    }

    public function render()
    {
        return view('livewire.search-product');
    }

    public function initPosFocus()
    {
        if ($this->posMode) {
            $this->dispatch('focus-product-search');
        }
    }

    public function updatedQuery()
    {
        $this->dismissScanAlerts(false);

        if ($this->posMode) {
            $this->dispatch('posSearchUpdated', query: $this->query);
        }

        if (blank($this->query)) {
            $this->search_results = Collection::empty();

            return;
        }

        if ($this->posMode) {
            return;
        }

        $this->search_results = Product::query()
            ->where(function ($query) {
                $query->where('product_name', 'like', '%' . $this->query . '%')
                    ->orWhere('product_code', 'like', '%' . $this->query . '%');
            })
            ->take($this->how_many)
            ->get();
    }

    public function scanProduct($code = null)
    {
        $code = trim($code ?? $this->query);

        if ($code === '') {
            return;
        }

        $this->query = $code;
        $this->dismissScanAlerts(false);

        $product = $this->resolveProductByCode($code);

        if ($product) {
            $this->handleResolvedProduct($product);

            return;
        }

        if ($this->posMode) {
            $this->scanNotFoundCode = $code;
            $this->dispatch('posProductNotFound');

            return;
        }

        $this->updatedQuery();
    }

    public function loadMore()
    {
        $this->how_many += 5;
        $this->updatedQuery();
    }

    public function resetQuery()
    {
        $this->query = '';
        $this->how_many = 5;
        $this->dismissScanAlerts(false);
        $this->search_results = Collection::empty();

        if ($this->posMode) {
            $this->dispatch('posSearchUpdated', query: '');
        }
    }

    public function dismissScanAlerts($refocus = true)
    {
        $this->scanNotFoundCode = null;
        $this->outOfStockProduct = null;

        if ($refocus) {
            $this->dispatch('focus-product-search');
        }
    }

    public function confirmAddOutOfStock()
    {
        if (!$this->outOfStockProduct) {
            return;
        }

        $this->dispatch('forceAddProduct', product: $this->outOfStockProduct);
        $this->outOfStockProduct = null;
        $this->resetQuery();
        $this->dispatch('focus-product-search');
    }

    protected $listeners = [
        'posClearSearch' => 'resetQuery',
        'posShowOutOfStock' => 'showOutOfStock',
    ];

    public function showOutOfStock($product)
    {
        $this->outOfStockProduct = is_array($product) ? $product : (array) $product;
        $this->dispatch('posProductOutOfStock');
    }

    public function selectProduct($productId)
    {
        $product = Product::findOrFail($productId);
        $this->handleResolvedProduct($product->toArray());
    }

    private function handleResolvedProduct($product): void
    {
        $payload = is_array($product) ? $product : $product->toArray();

        if ($this->posMode && (int) ($payload['product_quantity'] ?? 0) <= 0) {
            $this->outOfStockProduct = $payload;
            $this->dispatch('posProductOutOfStock');

            return;
        }

        $this->dispatch('productSelected', product: $payload);
        $this->resetQuery();
        $this->dispatch('focus-product-search');
    }

    private function resolveProductByCode(string $code): ?Product
    {
        $product = Product::where('product_code', $code)->first();

        if ($product) {
            return $product;
        }

        $results = Product::query()
            ->where(function ($query) use ($code) {
                $query->where('product_code', 'like', $code . '%')
                    ->orWhere('product_name', 'like', '%' . $code . '%');
            })
            ->take(2)
            ->get();

        if ($results->count() === 1) {
            return $results->first();
        }

        return null;
    }
}

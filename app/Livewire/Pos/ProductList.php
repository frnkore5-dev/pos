<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Product\Entities\Product;

class ProductList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'selectedCategory' => 'categoryChanged',
        'showCountChanged'  => 'showCountChanged',
        'posSearchUpdated'  => 'searchUpdated',
        'posBrowseAll'      => 'browseAll',
    ];

    public $categories;
    public $category_id;
    public $limit = 18;
    public $showGrid = false;
    public $searchQuery = '';
    public $browseAllActive = false;

    public function mount($categories)
    {
        $this->categories = $categories;
        $this->category_id = '';
    }

    public function render()
    {
        $products = $this->showGrid
            ? Product::query()
                ->when($this->category_id, fn ($query) => $query->where('category_id', $this->category_id))
                ->when($this->searchQuery, function ($query) {
                    $term = $this->searchQuery;
                    $query->where(function ($inner) use ($term) {
                        $inner->where('product_name', 'like', '%' . $term . '%')
                            ->orWhere('product_code', 'like', '%' . $term . '%');
                    });
                })
                ->paginate($this->limit)
            : collect();

        return view('livewire.pos.product-list', [
            'products' => $products,
        ]);
    }

    public function categoryChanged($category_id)
    {
        $this->category_id = $category_id ?: '';
        $this->browseAllActive = false;
        $this->updateGridVisibility();
        $this->resetPage();
    }

    public function searchUpdated($query = '')
    {
        $this->searchQuery = is_string($query) ? $query : '';
        $this->browseAllActive = false;
        $this->updateGridVisibility();
        $this->resetPage();
    }

    public function browseAll()
    {
        $this->browseAllActive = true;
        $this->category_id = '';
        $this->searchQuery = '';
        $this->showGrid = true;
        $this->dispatch('posClearSearch');
        $this->resetPage();
    }

    public function hideGrid()
    {
        $this->browseAllActive = false;
        $this->category_id = '';
        $this->searchQuery = '';
        $this->showGrid = false;
        $this->dispatch('posClearSearch');
        $this->dispatch('posCatalogHidden');
        $this->resetPage();
    }

    public function showCountChanged($value)
    {
        $this->limit = $value ?: 9999;
        $this->resetPage();
    }

    public function selectProduct($productId)
    {
        $product = Product::findOrFail($productId);

        $this->dispatch('productSelected', product: $product->toArray());
        $this->dispatch('focus-product-search');
    }

    private function updateGridVisibility(): void
    {
        $this->showGrid = $this->browseAllActive
            || filled($this->category_id)
            || filled(trim($this->searchQuery));
    }
}

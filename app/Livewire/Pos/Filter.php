<?php

namespace App\Livewire\Pos;

use Livewire\Component;

class Filter extends Component
{
    public $categories;
    public $category;
    public $showCount;

    protected $listeners = ['posCatalogHidden' => 'resetCategoryChip'];

    public function mount($categories)
    {
        $this->categories = $categories;
        $this->category = '';
        $this->showCount = 18;
    }

    public function render()
    {
        return view('livewire.pos.filter');
    }

    public function selectCategory($categoryId = '')
    {
        if ($categoryId === '' || $categoryId === null) {
            $this->category = '';
            $this->dispatch('selectedCategory', category_id: '');

            return;
        }

        $this->category = ($this->category == (string) $categoryId) ? '' : (string) $categoryId;
        $this->dispatch('selectedCategory', category_id: $this->category);
    }

    public function browseAllProducts()
    {
        $this->category = '';
        $this->dispatch('posBrowseAll');
    }

    public function resetCategoryChip()
    {
        $this->category = '';
    }

    public function updatedShowCount()
    {
        $this->dispatch('showCountChanged', value: $this->showCount);
    }
}

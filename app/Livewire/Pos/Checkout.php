<?php



namespace App\Livewire\Pos;



use Gloudemans\Shoppingcart\Facades\Cart;

use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Str;

use Livewire\Component;

use Modules\People\Entities\Customer;



class Checkout extends Component

{



    public $listeners = [

        'productSelected',

        'forceAddProduct',

        'discountModalRefresh',

        'posProceedCheckout' => 'proceed',

        'posResetCart' => 'resetCart',

        'posIncrementLastItem' => 'incrementLastItem',

        'posDecrementLastItem' => 'decrementLastItem',

    ];



    public $cart_instance;

    public $global_discount;

    public $global_tax;

    public $shipping;

    public $quantity;

    public $check_quantity;

    public $discount_type;

    public $item_discount;

    public $data;

    public $customer_id;

    public $total_amount;

    public $lastCartRowId;

    public $lastProductId;

    public $showCustomerModal = false;

    public $new_customer_name = '';

    public $new_customer_phone = '';

    public $customerSearch = '';



    public function mount($cartInstance, $customers, $defaultCustomerId = null) {

        $this->cart_instance = $cartInstance;

        $this->customer_id = $defaultCustomerId;

        $this->global_discount = 0;

        $this->global_tax = 0;

        $this->shipping = 0.00;

        $this->check_quantity = [];

        $this->quantity = [];

        $this->discount_type = [];

        $this->item_discount = [];

        $this->total_amount = 0;

        $this->lastCartRowId = null;

        $this->lastProductId = null;

    }



    public function hydrate() {

        $this->total_amount = $this->calculateTotal();

    }



    public function render() {

        $customersQuery = Customer::query()->orderBy('customer_name');



        if ($this->customerSearch !== '') {

            $term = $this->customerSearch;

            $customersQuery->where(function ($query) use ($term) {

                $query->where('customer_name', 'like', '%' . $term . '%')

                    ->orWhere('customer_phone', 'like', '%' . $term . '%');

            });

        }



        return view('livewire.pos.checkout', [

            'cart_items' => Cart::instance($this->cart_instance)->content(),

            'customers' => $customersQuery->get(['id', 'customer_name']),

            'heldSales' => $this->getHeldSales(),

        ]);

    }



    public function proceed() {

        if ($this->customer_id != null) {

            $this->dispatch('showCheckoutModal');

        } else {

            session()->flash('message', __('controller_messages.please_select_customer'));

        }

    }



    public function calculateTotal() {

        return Cart::instance($this->cart_instance)->total() + $this->shipping;

    }



    public function resetCart() {

        Cart::instance($this->cart_instance)->destroy();

        $this->lastCartRowId = null;

        $this->lastProductId = null;

        $this->quantity = [];

        $this->check_quantity = [];

        $this->discount_type = [];

        $this->item_discount = [];

        $this->total_amount = $this->calculateTotal();

        $this->dispatch('focus-product-search');

    }



    public function holdSale() {

        $cart = Cart::instance($this->cart_instance);



        if ($cart->count() === 0) {

            session()->flash('message', __('sale::messages.cart_empty'));



            return;

        }



        $held = $this->getHeldSales();

        $held[] = [

            'id' => (string) Str::uuid(),

            'held_at' => now()->format('H:i'),

            'customer_id' => $this->customer_id,

            'customer_name' => Customer::find($this->customer_id)?->customer_name ?? '?',

            'global_tax' => $this->global_tax,

            'global_discount' => $this->global_discount,

            'shipping' => $this->shipping,

            'quantity' => $this->quantity,

            'check_quantity' => $this->check_quantity,

            'discount_type' => $this->discount_type,

            'item_discount' => $this->item_discount,

            'items' => $cart->content()->map(function ($item) {

                return [

                    'id' => $item->id,

                    'name' => $item->name,

                    'qty' => $item->qty,

                    'price' => $item->price,

                    'weight' => $item->weight,

                    'options' => $item->options->toArray(),

                ];

            })->values()->all(),

            'total' => $cart->total() + (float) $this->shipping,

        ];



        $this->saveHeldSales($held);

        $this->resetCartState();

        session()->flash('message', __('sale::messages.sale_held'));

        $this->dispatch('focus-product-search');

    }



    public function resumeHeld($heldId) {

        $held = $this->getHeldSales();

        $sale = collect($held)->firstWhere('id', $heldId);



        if (!$sale) {

            return;

        }



        if (Cart::instance($this->cart_instance)->count() > 0) {

            session()->flash('message', __('sale::messages.clear_cart_before_resume'));



            return;

        }



        Cart::instance($this->cart_instance)->destroy();



        foreach ($sale['items'] as $item) {

            Cart::instance($this->cart_instance)->add([

                'id' => $item['id'],

                'name' => $item['name'],

                'qty' => $item['qty'],

                'price' => $item['price'],

                'weight' => $item['weight'] ?? 1,

                'options' => $item['options'],

            ]);

        }



        $this->customer_id = $sale['customer_id'];

        $this->global_tax = $sale['global_tax'];

        $this->global_discount = $sale['global_discount'];

        $this->shipping = $sale['shipping'];

        $this->quantity = $sale['quantity'];

        $this->check_quantity = $sale['check_quantity'];

        $this->discount_type = $sale['discount_type'];

        $this->item_discount = $sale['item_discount'];



        Cart::instance($this->cart_instance)->setGlobalTax((integer) $this->global_tax);

        Cart::instance($this->cart_instance)->setGlobalDiscount((integer) $this->global_discount);



        $lastItem = Cart::instance($this->cart_instance)->content()->last();

        $this->lastCartRowId = $lastItem?->rowId;

        $this->lastProductId = $lastItem?->id;



        $held = array_values(array_filter($held, fn ($h) => $h['id'] !== $heldId));

        $this->saveHeldSales($held);



        $this->total_amount = $this->calculateTotal();

        session()->flash('message', __('sale::messages.sale_resumed'));

        $this->dispatch('focus-product-search');

    }



    public function deleteHeld($heldId) {

        $held = array_values(array_filter(

            $this->getHeldSales(),

            fn ($h) => $h['id'] !== $heldId

        ));



        $this->saveHeldSales($held);

    }



    public function openCustomerModal() {

        $this->new_customer_name = '';

        $this->new_customer_phone = '';

        $this->customerSearch = '';

        $this->showCustomerModal = true;

    }



    public function closeCustomerModal() {

        $this->showCustomerModal = false;

    }



    public function selectCustomerFromModal($customerId) {

        $this->customer_id = $customerId;

        $this->showCustomerModal = false;

        $this->customerSearch = '';

    }



    public function quickCreateCustomer() {

        abort_if(Gate::denies('create_customers'), 403);



        $this->validate([

            'new_customer_name' => 'required|string|max:255',

            'new_customer_phone' => 'nullable|string|max:255',

        ]);



        $customer = Customer::create([

            'customer_name' => $this->new_customer_name,

            'customer_phone' => $this->new_customer_phone ?: 'N/A',

            'customer_email' => 'pos-' . Str::uuid() . '@local.invalid',

            'city' => 'N/A',

            'country' => 'N/A',

            'address' => 'N/A',

        ]);



        $this->customer_id = $customer->id;

        $this->showCustomerModal = false;

        $this->new_customer_name = '';

        $this->new_customer_phone = '';

        session()->flash('message', __('sale::messages.customer_created_quick'));

    }



    public function incrementLastItem() {

        if (!$this->lastCartRowId || !$this->lastProductId) {

            return;

        }



        $this->quantity[$this->lastProductId] = ($this->quantity[$this->lastProductId] ?? 1) + 1;

        $this->updateQuantity($this->lastCartRowId, $this->lastProductId);

        $this->total_amount = $this->calculateTotal();

    }



    public function decrementLastItem() {

        if (!$this->lastCartRowId || !$this->lastProductId) {

            return;

        }



        if (($this->quantity[$this->lastProductId] ?? 1) <= 1) {

            return;

        }



        $this->quantity[$this->lastProductId]--;

        $this->updateQuantity($this->lastCartRowId, $this->lastProductId);

        $this->total_amount = $this->calculateTotal();

    }



    public function productSelected($product) {

        $this->addProductToCart($product);

    }



    public function forceAddProduct($product) {

        $this->addProductToCart($product, true);

    }



    private function addProductToCart($product, bool $ignoreStock = false) {

        if (is_object($product)) {

            $product = (array) $product;

        }



        if (!$ignoreStock && (int) ($product['product_quantity'] ?? 0) <= 0) {

            $this->dispatch('posShowOutOfStock', product: $product);



            return;

        }



        $cart = Cart::instance($this->cart_instance);



        $exists = $cart->search(function ($cartItem, $rowId) use ($product) {

            return $cartItem->id == $product['id'];

        });



        if ($exists->isNotEmpty()) {

            session()->flash('message', __('controller_messages.product_exists_in_cart'));

            $this->dispatch('focus-product-search');



            return;

        }



        $rowId = $cart->add([

            'id'      => $product['id'],

            'name'    => $product['product_name'],

            'qty'     => 1,

            'price'   => $this->calculate($product)['price'],

            'weight'  => 1,

            'options' => [

                'product_discount'      => 0.00,

                'product_discount_type' => 'fixed',

                'sub_total'             => $this->calculate($product)['sub_total'],

                'code'                  => $product['product_code'],

                'stock'                 => $product['product_quantity'],

                'unit'                  => $product['product_unit'],

                'product_tax'           => $this->calculate($product)['product_tax'],

                'unit_price'            => $this->calculate($product)['unit_price']

            ]

        ]);



        $this->lastCartRowId = $rowId;

        $this->lastProductId = $product['id'];

        $this->check_quantity[$product['id']] = $product['product_quantity'];

        $this->quantity[$product['id']] = 1;

        $this->discount_type[$product['id']] = 'fixed';

        $this->item_discount[$product['id']] = 0;

        $this->total_amount = $this->calculateTotal();

        $this->dispatch('posProductAdded');

        $this->dispatch('focus-product-search');

    }



    public function removeItem($row_id) {

        Cart::instance($this->cart_instance)->remove($row_id);



        if ($this->lastCartRowId === $row_id) {

            $lastItem = Cart::instance($this->cart_instance)->content()->last();

            $this->lastCartRowId = $lastItem?->rowId;

            $this->lastProductId = $lastItem?->id;

        }



        $this->total_amount = $this->calculateTotal();

    }



    public function updatedGlobalTax() {

        Cart::instance($this->cart_instance)->setGlobalTax((integer)$this->global_tax);

    }



    public function updatedGlobalDiscount() {

        Cart::instance($this->cart_instance)->setGlobalDiscount((integer)$this->global_discount);

    }



    public function updateQuantity($row_id, $product_id) {

        if ($this->check_quantity[$product_id] < $this->quantity[$product_id]) {

            session()->flash('message', __('controller_messages.requested_quantity_not_available'));



            return;

        }



        Cart::instance($this->cart_instance)->update($row_id, $this->quantity[$product_id]);



        $cart_item = Cart::instance($this->cart_instance)->get($row_id);



        Cart::instance($this->cart_instance)->update($row_id, [

            'options' => [

                'sub_total'             => $cart_item->price * $cart_item->qty,

                'code'                  => $cart_item->options->code,

                'stock'                 => $cart_item->options->stock,

                'unit'                  => $cart_item->options->unit,

                'product_tax'           => $cart_item->options->product_tax,

                'unit_price'            => $cart_item->options->unit_price,

                'product_discount'      => $cart_item->options->product_discount,

                'product_discount_type' => $cart_item->options->product_discount_type,

            ]

        ]);



        $this->lastCartRowId = $row_id;

        $this->lastProductId = $product_id;

        $this->total_amount = $this->calculateTotal();

    }



    public function updatedDiscountType($value, $name) {

        $this->item_discount[$name] = 0;

    }



    public function discountModalRefresh($product_id, $row_id) {

        $this->updateQuantity($row_id, $product_id);

    }



    public function setProductDiscount($row_id, $product_id) {

        $cart_item = Cart::instance($this->cart_instance)->get($row_id);



        if ($this->discount_type[$product_id] == 'fixed') {

            Cart::instance($this->cart_instance)

                ->update($row_id, [

                    'price' => ($cart_item->price + $cart_item->options->product_discount) - $this->item_discount[$product_id]

                ]);



            $discount_amount = $this->item_discount[$product_id];



            $this->updateCartOptions($row_id, $product_id, $cart_item, $discount_amount);

        } elseif ($this->discount_type[$product_id] == 'percentage') {

            $discount_amount = ($cart_item->price + $cart_item->options->product_discount) * ($this->item_discount[$product_id] / 100);



            Cart::instance($this->cart_instance)

                ->update($row_id, [

                    'price' => ($cart_item->price + $cart_item->options->product_discount) - $discount_amount

                ]);



            $this->updateCartOptions($row_id, $product_id, $cart_item, $discount_amount);

        }



        session()->flash('discount_message' . $product_id, __('controller_messages.discount_added_to_product'));

    }



    public function calculate($product) {

        $price = 0;

        $unit_price = 0;

        $product_tax = 0;

        $sub_total = 0;



        if ($product['product_tax_type'] == 1) {

            $price = $product['product_price'] + ($product['product_price'] * ($product['product_order_tax'] / 100));

            $unit_price = $product['product_price'];

            $product_tax = $product['product_price'] * ($product['product_order_tax'] / 100);

            $sub_total = $product['product_price'] + ($product['product_price'] * ($product['product_order_tax'] / 100));

        } elseif ($product['product_tax_type'] == 2) {

            $price = $product['product_price'];

            $unit_price = $product['product_price'] - ($product['product_price'] * ($product['product_order_tax'] / 100));

            $product_tax = $product['product_price'] * ($product['product_order_tax'] / 100);

            $sub_total = $product['product_price'];

        } else {

            $price = $product['product_price'];

            $unit_price = $product['product_price'];

            $product_tax = 0.00;

            $sub_total = $product['product_price'];

        }



        return ['price' => $price, 'unit_price' => $unit_price, 'product_tax' => $product_tax, 'sub_total' => $sub_total];

    }



    public function updateCartOptions($row_id, $product_id, $cart_item, $discount_amount) {

        Cart::instance($this->cart_instance)->update($row_id, ['options' => [

            'sub_total'             => $cart_item->price * $cart_item->qty,

            'code'                  => $cart_item->options->code,

            'stock'                 => $cart_item->options->stock,

            'unit'                 => $cart_item->options->unit,

            'product_tax'           => $cart_item->options->product_tax,

            'unit_price'            => $cart_item->options->unit_price,

            'product_discount'      => $discount_amount,

            'product_discount_type' => $this->discount_type[$product_id],

        ]]);

    }



    private function getHeldSales(): array {

        return session('pos_held_sales.' . auth()->id(), []);

    }



    private function saveHeldSales(array $held): void {

        session(['pos_held_sales.' . auth()->id() => $held]);

    }



    private function resetCartState(): void {

        Cart::instance($this->cart_instance)->destroy();

        $this->global_discount = 0;

        $this->global_tax = 0;

        $this->shipping = 0.00;

        $this->quantity = [];

        $this->check_quantity = [];

        $this->discount_type = [];

        $this->item_discount = [];

        $this->lastCartRowId = null;

        $this->lastProductId = null;

        $this->customer_id = settings()->default_customer_id ?? Customer::orderBy('id')->value('id');

        $this->total_amount = $this->calculateTotal();

    }

}


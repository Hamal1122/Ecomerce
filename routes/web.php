<?php

use App\Livewire\CartPage;
use App\Livewire\CategoryPage;
use App\Livewire\CheckoutPage;
use App\Livewire\HomePage;
use App\Livewire\MyOrderDetailPage;
use App\Livewire\MyOrderPage;
use App\Livewire\ProductDetailPage;
use App\Livewire\ProductPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class);
Route::get('/categories', CategoryPage::class);
Route::get('/products', ProductPage::class);
Route::get('/cart', CartPage::class);
Route::get('/products/{products}', ProductDetailPage::class)->name('products.show');
Route::get('/checkout', CheckoutPage::class);
Route::get('/my-orders', MyOrderPage::class);
Route::get('/my-orders/{order}', MyOrderDetailPage::class);

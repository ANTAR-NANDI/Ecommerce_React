<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('warehouses', WarehouseController::class)->except('show');
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::resource('brands', BrandController::class)->except('show');
    Route::resource('colors', ColorController::class)->except('show');
    Route::resource('sizes', SizeController::class)->except('show');
    Route::resource('units', UnitController::class)->except('show');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('subcategories', [SubcategoryController::class, 'index'])->name('subcategories.index');
    Route::get('subcategories/create', [SubcategoryController::class, 'create'])->name('subcategories.create');
    Route::post('subcategories', [SubcategoryController::class, 'store'])->name('subcategories.store');
    Route::get('subcategories/{subcategory}/edit', [SubcategoryController::class, 'edit'])->name('subcategories.edit');
    Route::put('subcategories/{subcategory}', [SubcategoryController::class, 'update'])->name('subcategories.update');
    Route::delete('subcategories/{subcategory}', [SubcategoryController::class, 'destroy'])->name('subcategories.destroy');
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::get('media/picker', [MediaController::class, 'picker'])->name('media.picker');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
});

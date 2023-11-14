<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index($storeId)
    {
        try {
            $store = Store::findOrFail($storeId);
            $products = $store->products;

            return new DataResource(['products' => $products], 'Product list retrieved successfully', 200);
        } catch (\Exception $e) {
            // return new DataResource([], 'Error: ' . $e->getMessage(), 500);
            return new DataResource([], 'Store or Product not Found', 500);
        }
    }

    public function show($storeId, $productId)
    {
        try {
            $product = Product::whereHas('stores', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })->findOrFail($productId);

            return new DataResource(['product' => $product], 'Product retrieved successfully', 200);
        } catch (\Exception $e) {
            // return new DataResource([], 'Error: ' . $e->getMessage(), 500);
            return new DataResource([], 'Store or Product not Found', 500);
        }
    }

    public function store(Request $request, $storeId)
    {
        try {
            $store = Store::findOrFail($storeId);

            $this->validate($request, [
                'name' => 'required|string|max:255',
                // Add other validation rules as needed
            ]);

            $product = $store->products()->create($request->all());

            return new DataResource(['product' => $product], 'Product created successfully', 201);
        } catch (\Exception $e) {
            // return new DataResource([], 'Error: ' . $e->getMessage(), 500);
            return new DataResource([], 'Store or Product not Found', 500);
        }
    }

    public function update(Request $request, $storeId, $productId)
    {
        try {
            $store = Store::findOrFail($storeId);
            $product = $store->products()->findOrFail($productId);

            $this->validate($request, [
                'name' => 'required|string|max:255',
                // Add other validation rules as needed
            ]);

            $product->update($request->all());

            return new DataResource(['product' => $product], 'Product updated successfully', 200);
        } catch (\Exception $e) {
            // return new DataResource([], 'Error: ' . $e->getMessage(), 500);
            return new DataResource([], 'Store or Product not Found', 500);
        }
    }

    public function destroy($storeId, $productId)
    {
        try {
            $store = Store::findOrFail($storeId);
            $product = $store->products()->findOrFail($productId);

            $product->delete();

            return new DataResource([], 'Product deleted successfully', 200);
        } catch (\Exception $e) {
            // return new DataResource([], 'Error: ' . $e->getMessage(), 500);
            return new DataResource([], 'Store or Product not Found', 500);
        }
    }
}

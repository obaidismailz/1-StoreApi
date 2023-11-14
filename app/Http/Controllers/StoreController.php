<?php

// app/Http/Controllers/Api/StoreController.php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::with('products')->get();
        return new DataResource(['stores' => $stores], 'Stores list retrieved successfully', 200);
    }

    public function show($id)
    {
        $store = Store::with('products')->find($id);

        if (!$store) {
            return new DataResource([], 'Store not found', 404);
        }

        return new DataResource(['store' => $store], 'Store retrieved successfully', 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            // Add other validation rules as needed
        ]);

        $store = Store::create($request->all());

        return new DataResource(['store' => $store], 'Store registered successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return new DataResource([], 'Store not found', 404);
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            // Add other validation rules as needed
        ]);

        $store->update($request->all());

        return new DataResource(['store' => $store], 'Store updated successfully', 200);
    }

    public function destroy($id)
    {
        $store = Store::find($id);

        if (!$store) {
            return new DataResource([], 'Store not found', 404);
        }

        $store->delete();

        return new DataResource([], 'Store deleted successfully', 200);
    }

    public function Inventory($storeId)
    {
        // $store = Store::find($id);

        // if (!$store) {
        //     return new DataResource([], 'Store not found', 404);
        // }

        // $distinctProducts = $store->products()->distinct()->pluck('name');

        // return new DataResource(['distinct_products' => $distinctProducts], 'Distinct products retrieved successfully', 200);

        $store = Store::find($storeId);
        if (!$store) {
            return new DataResource([], 'Store not found', 404);
        }
        // $product = $store->products();
        // Get distinct product names
        $distinctProducts = $store->products()->distinct()->pluck('name');

        $result = [];

        // Iterate through distinct product names
        foreach ($distinctProducts as $productName) {
            // Count occurrences of each distinct product name
            $count = Product::where('name', $productName)->count();

            // Add result to array
            $result[] = [
                'name' => $productName,
                'count' => $count,
            ];
        }

        return new DataResource(['Inventory' => $result], 'Inventory retrieved successfully', 200);
    }
}

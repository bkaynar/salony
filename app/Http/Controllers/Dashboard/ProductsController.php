<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user || ! $user->salon_id) {
            return Inertia::render('Dashboard/Products/Index', ['products' => []]);
        }

        $products = Products::where('salon_id', $user->salon_id)
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'stock_level' => $p->stock_level,
                'price' => $p->price / 100,
                'cost' => $p->cost / 100,
            ]);

        return Inertia::render('Dashboard/Products/Index', ['products' => $products]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'stock_level' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
        ]);

        $validated['price'] = (int) ($validated['price'] * 100);
        $validated['cost'] = (int) ($validated['cost'] * 100);
        $validated['salon_id'] = $user->salon_id;

        Products::create($validated);
        return back()->with('success', 'Ürün eklendi');
    }

    public function update(Request $request, Products $product)
    {
        $user = Auth::user();
        if ($product->salon_id !== $user->salon_id) abort(403);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'nullable|string|max:100',
            'stock_level' => 'sometimes|integer|min:0',
            'price' => 'sometimes|numeric|min:0',
            'cost' => 'sometimes|numeric|min:0',
        ]);

        if (isset($validated['price'])) $validated['price'] = (int) ($validated['price'] * 100);
        if (isset($validated['cost'])) $validated['cost'] = (int) ($validated['cost'] * 100);

        $product->update($validated);
        return back()->with('success', 'Ürün güncellendi');
    }

    public function destroy(Products $product)
    {
        $user = Auth::user();
        if ($product->salon_id !== $user->salon_id) abort(403);
        $product->delete();
        return back()->with('success', 'Ürün silindi');
    }
}

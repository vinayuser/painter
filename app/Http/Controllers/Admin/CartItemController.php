<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartItemController extends Controller
{
    public function index(Request $request): View
    {
        $cartItems = CartItem::query()
            ->with(['user', 'product'])
            ->when($request->search, fn ($q, $s) => $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")))
            ->latest('updated_at')
            ->paginate(20);

        $stats = [
            'total_items' => CartItem::query()->sum('quantity'),
            'unique_carts' => CartItem::query()->distinct('user_id')->count('user_id'),
            'estimated_value' => CartItem::query()->with('product')->get()->sum(fn ($item) => ($item->product?->price ?? 0) * $item->quantity),
        ];

        return view('admin.carts.index', compact('cartItems', 'stats'));
    }
}

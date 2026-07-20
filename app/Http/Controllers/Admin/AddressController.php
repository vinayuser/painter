<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        $addresses = CustomerAddress::query()
            ->with('user')
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('recipient_name', 'like', "%{$s}%")
                    ->orWhere('city', 'like', "%{$s}%")
                    ->orWhere('pincode', 'like', "%{$s}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
            }))
            ->latest()
            ->paginate(20);

        return view('admin.addresses.index', compact('addresses'));
    }
}

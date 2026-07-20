<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $vendors = User::query()
            ->where('role', UserRole::Vendor)
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->withCount(['vendorProducts', 'vendorOrders'])
            ->latest()
            ->paginate(15);

        return view('admin.vendors.index', compact('vendors'));
    }

    public function create(): View
    {
        return view('admin.vendors.form', ['vendor' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateVendor($request);
        $data['role'] = UserRole::Vendor;
        User::query()->create($data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor account created successfully.');
    }

    public function show(User $vendor): View
    {
        if ($vendor->role !== UserRole::Vendor) {
            abort(404);
        }

        $vendor->load([
            'vendorProducts' => fn ($q) => $q->latest()->limit(10),
            'vendorOrders' => fn ($q) => $q->with('customer')->latest()->limit(10),
        ]);

        $stats = [
            'products' => $vendor->vendorProducts()->count(),
            'orders' => $vendor->vendorOrders()->count(),
            'revenue' => (float) $vendor->vendorOrders()->sum('total_amount'),
        ];

        return view('admin.vendors.show', compact('vendor', 'stats'));
    }

    public function edit(User $vendor): View
    {
        if ($vendor->role !== UserRole::Vendor) {
            abort(404);
        }

        return view('admin.vendors.form', compact('vendor'));
    }

    public function update(Request $request, User $vendor): RedirectResponse
    {
        if ($vendor->role !== UserRole::Vendor) {
            abort(404);
        }

        $vendor->update($this->validateVendor($request, $vendor));

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    private function validateVendor(Request $request, ?User $vendor = null): array
    {
        if ($request->filled('phone')) {
            $request->merge(['phone' => PhoneNumber::normalize($request->input('phone'))]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($vendor)],
            'phone' => ['nullable', 'string', 'size:10', 'regex:/^[6-9]\d{9}$/', \Illuminate\Validation\Rule::unique('users')->ignore($vendor)],
            'address' => ['nullable', 'string', 'max:1000'],
            'password' => [$vendor ? 'nullable' : 'required', 'string', 'min:8'],
            'is_active' => ['boolean'],
        ];

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_verified'] = true;

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } elseif ($vendor) {
            unset($data['password']);
        }

        return $data;
    }
}

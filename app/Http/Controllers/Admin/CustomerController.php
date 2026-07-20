<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = User::query()
            ->where('role', UserRole::Customer)
            ->withCount(['orders', 'addresses', 'painterBookings'])
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            }))
            ->when($request->verified !== null && $request->verified !== '', fn ($q) => $q->where('is_verified', (bool) $request->verified))
            ->latest()
            ->paginate(15);

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $user): View
    {
        if ($user->role !== UserRole::Customer) {
            abort(404);
        }

        $user->load([
            'orders' => fn ($q) => $q->latest()->limit(15),
            'addresses',
            'painterBookings' => fn ($q) => $q->with('painter')->latest()->limit(10),
        ]);

        return view('admin.customers.show', ['customer' => $user]);
    }
}

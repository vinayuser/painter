<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PainterController extends Controller
{
    public function index(Request $request): View
    {
        $painters = User::query()
            ->where('role', UserRole::Painter)
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%");
            }))
            ->when($request->verified !== null && $request->verified !== '', fn ($q) => $q->where('is_verified', (bool) $request->verified))
            ->withCount(['assignedBookings', 'portfolios'])
            ->latest()
            ->paginate(15);

        return view('admin.painters.index', compact('painters'));
    }

    public function show(User $user): View
    {
        if ($user->role !== UserRole::Painter) {
            abort(404);
        }

        $user->load(['portfolios', 'assignedBookings' => fn ($q) => $q->with('customer')->latest()->limit(10)]);

        return view('admin.painters.show', ['painter' => $user]);
    }
}

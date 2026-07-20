<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->role, fn ($q) => $q->where('role', $request->role))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User, 'roles' => UserRole::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request);
        User::query()->create($data);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $user->load([
            'orders' => fn ($q) => $q->latest()->limit(10),
            'addresses',
            'painterBookings' => fn ($q) => $q->latest()->limit(10),
            'portfolios',
        ]);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user, 'roles' => UserRole::cases()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validateUser($request, $user);
        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        if ($request->filled('phone')) {
            $request->merge(['phone' => PhoneNumber::normalize($request->input('phone'))]);
        }

        if ($request->filled('aadhar_number')) {
            $request->merge(['aadhar_number' => preg_replace('/\s+/', '', (string) $request->input('aadhar_number'))]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'phone' => ['nullable', 'string', 'size:10', 'regex:/^[6-9]\d{9}$/', Rule::unique('users')->ignore($user)],
            'address' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'role' => ['required', Rule::in(UserRole::values())],
            'is_active' => ['boolean'],
            'is_verified' => ['boolean'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'cost_per_hour' => ['nullable', 'numeric', 'gt:0'],
            'aadhar_number' => ['nullable', 'string', 'size:12', 'regex:/^\d{12}$/', Rule::unique('users')->ignore($user)],
            'specialization' => ['nullable', 'string', 'max:150'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:20'],
        ];

        if ($user) {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_verified'] = $request->boolean('is_verified');

        if (empty($data['password'])) {
            if ($user === null) {
                $data['password'] = Hash::make(Str::random(32));
            } else {
                unset($data['password']);
            }
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        if (! empty($data['experience_years'])) {
            $data['experience_text'] = $data['experience_years'].' years';
        }

        return $data;
    }
}

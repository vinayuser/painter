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

class DeliveryAgentController extends Controller
{
    public function index(Request $request): View
    {
        $agents = User::query()
            ->where('role', UserRole::DeliveryAgent)
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->withCount(['assignedDeliveries as completed_deliveries_count' => fn ($q) => $q->where('order_status', \App\Enums\OrderStatus::Delivered)])
            ->latest()
            ->paginate(15);

        return view('admin.delivery-agents.index', compact('agents'));
    }

    public function create(): View
    {
        return view('admin.delivery-agents.form', ['agent' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAgent($request);
        $data['role'] = UserRole::DeliveryAgent;
        User::query()->create($data);

        return redirect()->route('admin.delivery-agents.index')->with('success', 'Delivery partner created successfully.');
    }

    public function edit(User $delivery_agent): View
    {
        if ($delivery_agent->role !== UserRole::DeliveryAgent) {
            abort(404);
        }

        return view('admin.delivery-agents.form', ['agent' => $delivery_agent]);
    }

    public function update(Request $request, User $delivery_agent): RedirectResponse
    {
        if ($delivery_agent->role !== UserRole::DeliveryAgent) {
            abort(404);
        }

        $delivery_agent->update($this->validateAgent($request, $delivery_agent));

        return redirect()->route('admin.delivery-agents.index')->with('success', 'Delivery partner updated successfully.');
    }

    public function show(User $delivery_agent): View
    {
        if ($delivery_agent->role !== UserRole::DeliveryAgent) {
            abort(404);
        }

        $pastDeliveries = $delivery_agent->assignedDeliveries()
            ->with(['customer', 'vendor'])
            ->where('order_status', \App\Enums\OrderStatus::Delivered)
            ->latest()
            ->paginate(15);

        return view('admin.delivery-agents.show', ['agent' => $delivery_agent, 'pastDeliveries' => $pastDeliveries]);
    }

    private function validateAgent(Request $request, ?User $agent = null): array
    {
        if ($request->filled('phone')) {
            $request->merge(['phone' => PhoneNumber::normalize($request->input('phone'))]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($agent)],
            'phone' => ['nullable', 'string', 'size:10', 'regex:/^[6-9]\d{9}$/', \Illuminate\Validation\Rule::unique('users')->ignore($agent)],
            'address' => ['nullable', 'string', 'max:1000'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:20'],
            'password' => [$agent ? 'nullable' : 'required', 'string', 'min:8'],
            'is_active' => ['boolean'],
        ];

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_verified'] = true;

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } elseif ($agent) {
            unset($data['password']);
        }

        return $data;
    }
}

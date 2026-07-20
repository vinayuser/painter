@extends('admin.layout')

@section('title', $user->exists ? 'Edit User' : 'Add User')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $user->exists ? 'Edit User' : 'Add User' }}</h3>
        <div class="card-tools">
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-default"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="card-body">
    <form id="user-form" method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div class="form-row">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Phone (10-digit)</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="10">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Business Name (for vendors)</label>
                <input type="text" name="business_name" value="{{ old('business_name', $user->business_name) }}">
            </div>
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address">{{ old('address', $user->address) }}</textarea>
        </div>
        <div class="form-group">
            <label>Bio</label>
            <textarea name="bio">{{ old('bio', $user->bio) }}</textarea>
        </div>

        <h3>Painter fields</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Experience (years)</label>
                <input type="number" name="experience_years" value="{{ old('experience_years', $user->experience_years) }}" min="0" max="60">
            </div>
            <div class="form-group">
                <label>Cost per hour</label>
                <input type="number" step="0.01" name="cost_per_hour" value="{{ old('cost_per_hour', $user->cost_per_hour) }}">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Aadhar number</label>
                <input type="text" name="aadhar_number" value="{{ old('aadhar_number', $user->aadhar_number) }}" maxlength="12">
            </div>
            <div class="form-group">
                <label>Specialization</label>
                <input type="text" name="specialization" value="{{ old('specialization', $user->specialization) }}">
            </div>
        </div>

        <h3>Delivery agent fields</h3>
        <div class="form-row">
            <div class="form-group">
                <label>License number</label>
                <input type="text" name="license_number" value="{{ old('license_number', $user->license_number) }}">
            </div>
            <div class="form-group">
                <label>Vehicle number</label>
                <input type="text" name="vehicle_number" value="{{ old('vehicle_number', $user->vehicle_number) }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Password {{ $user->exists ? '(leave blank to keep)' : '(optional for API users)' }}</label>
                <input type="password" name="password">
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
                    Active
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $user->is_verified ?? false))>
                    Phone verified
                </label>
            </div>
        </div>
    </form>
    </div>
    <div class="card-footer">
        <button type="submit" form="user-form" class="btn btn-primary"><i class="fas fa-save"></i> Save User</button>
    </div>
</div>
@endsection

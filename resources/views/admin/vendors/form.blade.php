@extends('admin.layout')

@section('title', $vendor->exists ? 'Edit Vendor' : 'Create Vendor')

@section('content')
<div class="page-header">
    <h1>{{ $vendor->exists ? 'Edit Vendor' : 'Create Vendor Account' }}</h1>
    <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <form method="POST" action="{{ $vendor->exists ? route('admin.vendors.update', $vendor) : route('admin.vendors.store') }}">
        @csrf
        @if($vendor->exists) @method('PUT') @endif
        <div class="form-row">
            <div class="form-group"><label>Contact Name</label><input type="text" name="name" value="{{ old('name', $vendor->name) }}" required></div>
            <div class="form-group"><label>Business Name</label><input type="text" name="business_name" value="{{ old('business_name', $vendor->business_name) }}"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Email (login)</label><input type="email" name="email" value="{{ old('email', $vendor->email) }}" required></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}" maxlength="10"></div>
        </div>
        <div class="form-group"><label>Address</label><textarea name="address">{{ old('address', $vendor->address) }}</textarea></div>
        <div class="form-row">
            <div class="form-group">
                <label>Password {{ $vendor->exists ? '(leave blank to keep)' : '(required)' }}</label>
                <input type="password" name="password" {{ $vendor->exists ? '' : 'required' }}>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vendor->is_active ?? true))>
                    Active
                </label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Vendor</button>
    </form>
</div>
@endsection

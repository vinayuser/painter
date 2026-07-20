@extends('admin.layout')

@section('title', $agent->exists ? 'Edit Delivery Partner' : 'Create Delivery Partner')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.delivery-agents.index') }}">Delivery Partners</a></li>
    <li class="breadcrumb-item active">{{ $agent->exists ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-shipping-fast mr-1"></i>
            {{ $agent->exists ? 'Edit Delivery Partner' : 'Create Delivery Partner' }}
        </h3>
        <div class="card-tools">
            <a href="{{ route('admin.delivery-agents.index') }}" class="btn btn-sm btn-default">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <form id="agent-form" method="POST" action="{{ $agent->exists ? route('admin.delivery-agents.update', $agent) : route('admin.delivery-agents.store') }}">
        @csrf
        @if($agent->exists) @method('PUT') @endif
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $agent->name) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email (login) <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $agent->email) }}" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Phone (10-digit)</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $agent->phone) }}" maxlength="10" placeholder="9876543210">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>License Number</label>
                        <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $agent->license_number) }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Vehicle Number</label>
                        <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number', $agent->vehicle_number) }}" placeholder="MH 12 AB 1234">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Password {{ $agent->exists ? '(leave blank to keep)' : '(required)' }}</label>
                        <input type="password" name="password" class="form-control" {{ $agent->exists ? '' : 'required' }} autocomplete="new-password">
                    </div>
                </div>
            </div>
            <div class="form-group mb-0">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $agent->is_active ?? true))>
                    <label class="custom-control-label" for="is_active">Active — partner can log in and accept deliveries</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Partner
            </button>
            <a href="{{ route('admin.delivery-agents.index') }}" class="btn btn-default">Cancel</a>
        </div>
    </form>
</div>
@endsection

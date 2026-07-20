@extends('admin.layout')

@section('title', 'Users')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Users</h3>
        <div class="card-tools">
            <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add User</a>
        </div>
    </div>
    <div class="card-body">
    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." class="form-control form-control-sm">
        <select name="role" class="form-control form-control-sm" style="width:auto;">
            <option value="">All Roles</option>
            @foreach(\App\Enums\UserRole::cases() as $role)
                <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-filter"></i> Filter</button>
    </form>

    <div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?? '-' }}</td>
                    <td><span class="badge badge-blue">{{ $user->role->label() }}</span></td>
                    <td>{{ $user->is_active ? 'Yes' : 'No' }}</td>
                    <td class="actions">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-xs btn-info" title="View"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-default" title="Edit"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    </div>
    <div class="card-footer">{{ $users->withQueryString()->links() }}</div>
</div>
@endsection

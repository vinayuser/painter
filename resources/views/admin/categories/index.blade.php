@extends('admin.layout')

@section('title', 'Categories')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Categories</h3>
        <div class="card-tools">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add Category</a>
        </div>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td><a href="{{ route('admin.categories.show', $category) }}">{{ $category->name }}</a></td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td><span class="badge badge-blue">{{ $category->products_count }}</span></td>
                        <td>{{ $category->is_active ? 'Yes' : 'No' }}</td>
                        <td class="actions">
                            <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-xs btn-default"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $categories->links() }}</div>
</div>
@endsection

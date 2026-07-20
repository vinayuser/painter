@extends('admin.layout')

@section('title', $category->exists ? 'Edit Category' : 'Add Category')

@section('content')
<div class="page-header">
    <h1>{{ $category->exists ? 'Edit Category' : 'Add Category' }}</h1>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if($category->exists) @method('PUT') @endif

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required>
        </div>
        <div class="form-group">
            <label>Slug (optional)</label>
            <input type="text" name="slug" value="{{ old('slug', $category->slug) }}">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description">{{ old('description', $category->description) }}</textarea>
        </div>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
                Active
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection

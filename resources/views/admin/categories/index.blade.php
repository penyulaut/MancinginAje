@extends('layouts.main')

@section('content')
  <x-sidebar></x-sidebar>

  <div class="admin-content" style="margin-left: 250px; padding: 30px; min-height: calc(100vh - 120px); background: #f8fafc;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1 fw-bold" style="color: #1e293b;">Categories</h2>
        <p class="text-muted mb-0">Manage product categories</p>
      </div>
      <a href="{{ route('admin.categories.create') }}" class="btn btn-success px-4" style="background: linear-gradient(90deg, #10b981 0%, #059669 100%); border: none; border-radius: 10px;">
        <i class="fas fa-plus me-2"></i>New Category
      </a>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0" style="border-collapse: separate; border-spacing: 0;">
            <thead>
              <tr style="background: #f1f5f9;">
                <th class="py-3 px-4 border-0" style="color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Name</th>
                <th class="py-3 px-4 border-0" style="color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Slug</th>
                <th class="py-3 px-4 border-0" style="color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Products Count</th>
                <th class="py-3 px-4 border-0 text-end" style="color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($categories as $category)
                <tr>
                  <td class="py-3 px-4 border-0 align-middle">
                    <span class="fw-semibold" style="color: #1e293b;">{{ $category->nama }}</span>
                  </td>
                  <td class="py-3 px-4 border-0 align-middle">
                    <code style="background: #f1f5f9; padding: 4px 10px; border-radius: 6px; color: #64748b;">{{ $category->slug }}</code>
                  </td>
                  <td class="py-3 px-4 border-0 align-middle">
                    <span class="badge" style="background: linear-gradient(90deg, #10b981 0%, #059669 100%); padding: 6px 12px; border-radius: 20px; font-weight: 500;">
                      {{ $category->products_count }} Products
                    </span>
                  </td>
                  <td class="py-3 px-4 border-0 align-middle text-end">
                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-light me-1" style="border-radius: 8px;" title="Edit">
                      <i class="fas fa-pencil-alt text-primary"></i>
                    </a>
                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-light" style="border-radius: 8px;" title="Delete">
                        <i class="fas fa-trash text-danger"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center py-5 text-muted">
                    <i class="fas fa-tags fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                    Belum ada kategori. <a href="{{ route('admin.categories.create') }}">Buat kategori pertama</a>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

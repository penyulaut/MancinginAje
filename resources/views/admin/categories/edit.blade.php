@extends('layouts.main')

@section('content')
  <x-sidebar></x-sidebar>

  <div class="admin-content" style="margin-left: 250px; padding: 30px; min-height: calc(100vh - 120px); background: #f8fafc;">
    <!-- Back Link -->
    <a href="{{ route('admin.categories.index') }}" class="text-decoration-none mb-3 d-inline-flex align-items-center" style="color: #64748b;">
      <i class="fas fa-arrow-left me-2"></i>Back to Categories
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
      <div>
        <h2 class="mb-1 fw-bold" style="color: #1e293b;">Edit Category</h2>
        <p class="text-muted mb-0">Update category information</p>
      </div>
      @if(!$category->hasProducts())
        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-outline-danger" style="border-radius: 10px;">
            <i class="fas fa-trash me-2"></i>Delete Category
          </button>
        </form>
      @endif
    </div>

    <div class="row">
      <!-- Category Details Form -->
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
          <div class="card-body p-4">
            <div class="d-flex align-items-center mb-4">
              <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(90deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-tag text-white"></i>
              </div>
              <h5 class="mb-0 fw-bold" style="color: #1e293b;">Category Details</h5>
            </div>

            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
              @csrf
              @method('PUT')

              <div class="mb-4">
                <label for="nama" class="form-label fw-semibold" style="color: #374151;">
                  Category Name <span class="text-danger">*</span>
                </label>
                <div class="input-group" style="border-radius: 10px; overflow: hidden; border: 1px solid #e5e7eb;">
                  <span class="input-group-text bg-white border-0" style="padding-left: 15px;">
                    <i class="fas fa-tag text-muted"></i>
                  </span>
                  <input type="text" class="form-control border-0 py-3 @error('nama') is-invalid @enderror" 
                         id="nama" name="nama" value="{{ old('nama', $category->nama) }}" 
                         placeholder="Enter category name" required>
                </div>
                @error('nama')
                  <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-4">
                <label for="slug" class="form-label fw-semibold" style="color: #374151;">
                  Slug <span class="text-danger">*</span>
                </label>
                <div class="input-group" style="border-radius: 10px; overflow: hidden; border: 1px solid #e5e7eb;">
                  <span class="input-group-text bg-white border-0" style="padding-left: 15px;">
                    <i class="fas fa-link text-muted"></i>
                  </span>
                  <input type="text" class="form-control border-0 py-3 @error('slug') is-invalid @enderror" 
                         id="slug" name="slug" value="{{ old('slug', $category->slug) }}" 
                         placeholder="category-slug" required>
                </div>
                <small class="text-muted">The URL-friendly version of the name. Must be unique.</small>
                @error('slug')
                  <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
              </div>

              <button type="submit" class="btn btn-success w-100 py-3" style="background: linear-gradient(90deg, #10b981 0%, #059669 100%); border: none; border-radius: 10px; font-weight: 600;">
                <i class="fas fa-save me-2"></i>Save Changes
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Preview & Statistics -->
      <div class="col-lg-5">
        <!-- Preview Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
          <div class="card-body p-4">
            <div class="d-flex align-items-center mb-4">
              <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: #e0f2fe;">
                <i class="fas fa-eye" style="color: #0ea5e9;"></i>
              </div>
              <h5 class="mb-0 fw-bold" style="color: #1e293b;">Preview</h5>
            </div>

            <div class="mb-3">
              <div class="text-muted small text-uppercase mb-1" style="letter-spacing: 0.5px;">Display Name</div>
              <div class="fw-bold fs-5" style="color: #1e293b;">{{ $category->nama }}</div>
            </div>

            <div>
              <div class="text-muted small text-uppercase mb-1" style="letter-spacing: 0.5px;">URL Structure</div>
              <div>
                <span class="text-muted">/categories/</span><span style="color: #10b981; font-weight: 600;">{{ $category->slug }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistics Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-body p-4">
            <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Statistics</h6>

            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
              <span style="color: #64748b;">Total Products</span>
              <span class="fw-bold" style="color: #10b981; font-size: 1.25rem;">{{ $category->products_count }}</span>
            </div>

            @if($category->hasProducts())
              <div class="alert mb-0" style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 10px; color: #92400e;">
                <i class="fas fa-exclamation-circle me-2"></i>
                <small>This category contains products. You cannot delete it until all products are moved or deleted.</small>
              </div>
            @else
              <div class="alert mb-0" style="background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 10px; color: #065f46;">
                <i class="fas fa-check-circle me-2"></i>
                <small>This category has no products and can be safely deleted.</small>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Auto-generate slug from name
    document.getElementById('nama').addEventListener('input', function() {
      const slug = this.value
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
      document.getElementById('slug').value = slug;
    });
  </script>
@endsection

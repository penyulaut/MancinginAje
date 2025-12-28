@extends('layouts.main')

@section('content')
<div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold mb-2">MancinginAje</h2>
                            <p class="text-muted">Toko Alat Pancing Online Terpercaya</p>
                        </div>

                        <h4 class="mb-4 text-center">Daftar Akun Baru</h4>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                    <p class="mb-0">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('register.submit') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Masukkan email" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Masukkan password (minimal 8 karakter)" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi password" required>
                            </div>

                            <!-- Role is automatically set to customer -->
                            <input type="hidden" name="role" value="customer">

                            <div class="mb-3">
                                <label for="phone" class="form-label">Nomor Telepon (Opsional)</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="Masukkan nomor telepon" value="{{ old('phone') }}">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat (Opsional)</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" placeholder="Masukkan alamat lengkap" rows="3">{{ old('address') }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-warning w-100 mb-3">Daftar</button>
                        </form>

                        <hr>

                        <p class="text-center text-muted mb-0">
                            Sudah punya akun? <a href="{{ route('login') }}" class="text-warning fw-bold">Masuk di sini</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  </body>
</html>
@extends('layouts.main')

@section('content')
<div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold mb-2">MancinginAje</h2>
                            <p class="text-muted">Toko Alat Pancing Online Terpercaya</p>
                        </div>

                        <h4 class="mb-4 text-center">Masuk ke Akun</h4>

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('login.submit') }}" method="POST">  
                            @csrf         
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Masukkan email" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Masukkan password" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>          
                            <button type="submit" class="btn btn-warning w-100 mb-3">Masuk</button>
                        </form>

                        <hr>

                        <p class="text-center text-muted mb-0">
                            Belum punya akun? <a href="{{ route('register.show') }}" class="text-warning fw-bold">Daftar di sini</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
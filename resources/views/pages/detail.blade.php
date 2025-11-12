@extends('layouts.main')

@section('content')
    <x-navbar/>
    <div class="container mt-4" style="height: 100vh">
        <div class="search-bar row">
            <div class="col-md-5">
                <img src="{{ asset($product->gambar) }}" class="img-fluid rounded">
            </div>
            <div class="col-md-7">
                <h2>{{ $product->nama }}</h2>
                <p>{{ $product->deskripsi }}</p>
                <h4>Rp {{ number_format($product->harga) }}</h4>

                <form action="{{ route('cart.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="number" name="quantity" value="1" min="1" class="form-control w-25 mb-3">
                    <button type="submit" class="btn btn-success">Add to Cart</button>
                </form>
            </div>
        </div>
    </div>
@endsection
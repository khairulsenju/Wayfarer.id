@extends('layouts.admin')

@section('title', 'Ubah Produk')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Ubah Produk</h1>

    @include('admin.products._form', [
        'action' => route('admin.products.update', $product),
        'method' => 'PUT',
        'product' => $product,
        'categories' => $categories,
        'modes' => $modes,
    ])
@endsection
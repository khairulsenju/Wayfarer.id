@extends('layouts.admin')

@section('title', 'Tambah Produk')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Tambah Produk</h1>

    @include('admin.products._form', [
        'action' => route('admin.products.store'),
        'method' => 'POST',
        'categories' => $categories,
        'modes' => $modes,
    ])
@endsection
@extends('layouts.admin')

@section('title', 'Ubah Kategori')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Ubah Kategori</h1>

    @include('admin.categories._form', [
        'action' => route('admin.categories.update', $category),
        'method' => 'PUT',
        'category' => $category,
    ])
@endsection
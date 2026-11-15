@extends('layouts.admin')

@section('title', 'Tambah Kategori')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Tambah Kategori</h1>

    @include('admin.categories._form', [
        'action' => route('admin.categories.store'),
        'method' => 'POST',
    ])
@endsection
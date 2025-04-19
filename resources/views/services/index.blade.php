{{-- filepath: resources/views/services/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Jasa')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between mb-3">
        <h4>Daftar Jasa</h4>
        <a href="{{ route('services.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Tambah Jasa
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- filepath: resources/views/services/index.blade.php --}}
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Nama Jasa</th>
            <th>Harga</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($services as $service)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $service->name }}</td>
                <td>Rp {{ number_format($service->harga, 0, ',', '.') }}</td>
                <td>
                    <a href="{{ route('services.edit', $service->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jasa ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">Tidak ada jasa yang tersedia.</td>
            </tr>
        @endforelse
    </tbody>
</table>

    {{ $services->links() }}
</div>
@endsection
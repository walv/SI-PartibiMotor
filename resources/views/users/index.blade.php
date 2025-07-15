@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Pengguna</h5>
                <div>
                    <a href="{{ route('users.trashed') }}" class="btn btn-warning btn-sm me-2">
                        <i class="fas fa-trash-restore me-1"></i> Lihat Akun Dinonaktifkan
                    </a>
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Tambah Pengguna
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'info' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-info me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if ($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan pengguna ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data pengguna</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
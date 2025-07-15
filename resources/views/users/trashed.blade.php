@extends('layouts.app')

@section('title', 'Akun Dinonaktifkan')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Akun Dinonaktifkan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Dinonaktifkan Pada</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td> <!-- Tambahkan data nama -->
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td>{{ $user->deleted_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="d-flex">
                                    <form action="{{ route('users.restore', $user->id) }}" method="POST" class="me-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"
                                            onclick="return confirm('Aktifkan kembali akun ini?')">
                                            <i class="fas fa-trash-restore"></i> Restore
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada akun yang dinonaktifkan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
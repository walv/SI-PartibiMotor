@extends('layouts.app')

@section('content')
<div class="container py-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @elseif ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-user-circle me-2"></i>Profil Saya
                </h4>
                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'success' }} fs-6">
                    {{ strtoupper($user->role) }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Bagian Kiri (Foto Profil) -->
                <div class="col-md-4 text-center border-end">
                    <div class="mb-3">
                        @php
                            $avatarPath = $user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)
                                ? asset('storage/' . $user->avatar)
                                : asset('images/default-avatar.png');
                        @endphp
                        <img src="{{ $avatarPath }}" class="img-thumbnail rounded-circle shadow" width="150" alt="Foto Profil">
                    </div>
                    <a href="{{ route('account.edit') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit Profil
                    </a>
                </div>

                <!-- Bagian Kanan (Detail Profil) -->
                <div class="col-md-8">  
                    <table class="table table-hover">
                        <tr>
                            <th width="30%" class="bg-light">Nama</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Email</th>
                            <td>
                                {{ $user->email }}
                                @if ($user->email_verified_at)
                                    <span class="badge bg-success ms-2">
                                        <i class="fas fa-check-circle"></i> Terverifikasi
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Role</th>
                            <td>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'success' }}">
                                    {{ $user->role === 'admin' ? 'Administrator' : 'Kasir' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Bergabung Pada</th>
                            <td>{{ $user->created_at->translatedFormat('l, d F Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

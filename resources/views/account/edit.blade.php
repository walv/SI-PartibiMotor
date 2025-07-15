@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header bg-warning">
            <h4><i class="fas fa-user-edit me-2"></i>Edit Profil</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="text-center mb-4">
                <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png') }}"
                     class="img-thumbnail rounded-circle shadow" width="150" alt="Foto Profil"
                     onerror="this.src='{{ asset('images/default-avatar.png') }}'">
            </div>

            <form method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data">
                @csrf
                @method('POST')

                <div class="mb-3">
                    <label class="form-label">Foto Profil</label>
                    <input type="file" name="avatar" class="form-control">
                    <small class="text-muted">Format: JPEG, PNG (Maks. 2MB)</small>
                    @error('avatar')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" 
                           value="{{ old('name', $user->name) }}" 
                           class="form-control" required>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
                <a href="{{ route('account.show') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Batal
                </a>
            </form>
        </div>
    </div>
</div>
@endsection

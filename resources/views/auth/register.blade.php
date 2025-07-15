@extends('layouts.auth')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                 <div class="mb-3">
    <label for="name" class="form-label">Nama Lengkap *</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" 
           name="name" value="{{ old('name') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
 <div class="mb-3">
                            <label for="username" class="form-label">{{ __('Username') }}</label>
                            <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                                   name="username" value="{{ old('username') }}" required
                                   pattern="[a-z0-9_]+" 
                                   title="Hanya boleh huruf kecil, angka, atau underscore (_)"
                                   oninput="this.value = this.value.toLowerCase()">
                            @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="text-muted">Gunakan huruf kecil, angka, atau underscore (_)</small>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email Address') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                       <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

 <div class="mb-3">
                            <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                            <input id="password-confirm" type="password" class="form-control"
                                   name="password_confirmation" required>
                        </div>

                       @auth
                            @if(auth()->user()->role === 'admin')
                                <div class="mb-3">
                                    <label for="role" class="form-label">{{ __('Role') }}</label>
                                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                                        <option value="">-- Pilih Role --</option>
                                        <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                                        <option value="pemilik" {{ old('role') == 'pemilik' ? 'selected' : '' }}>Pemilik</option>
                                    </select>
                                    @error('role')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            @endif
                        @endauth
                        
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

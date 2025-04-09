<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEM INFORMASI - @yield('title')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar .nav-link:hover {
            color: white;
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .content {
            padding: 20px;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                @include('layouts.sidebar')
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 content">
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                    <div class="container-fluid">
                        <h4 class="mb-0">@yield('title')</h4>
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-3">{{ date('d F Y') }}</span>
                            @if (auth()->check())
                            <div class="dropdown">
                                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                                   id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-2"></i>
                                    <strong>{{ Auth::user()->name ?? 'User' }}</strong>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownUser1">
                                    @if (Route::has('change.password'))
                                        <li>
                                            <a class="dropdown-item" href="{{ route('change.password') }}">
                                                <i class="fas fa-key me-2"></i> Ganti Password
                                            </a>
                                        </li>
                                    @endif
                        
                                    <li><hr class="dropdown-divider"></li>
                        
                                    @if (Route::has('logout'))
                                        <li>
                                            <a class="dropdown-item" href="#"
                                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                <i class="fas fa-sign-out-alt me-2"></i> Keluar
                                            </a>
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                                @csrf
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                        </div>
                    </div>
                </nav>
                
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @yield('scripts')
</body>
</html>

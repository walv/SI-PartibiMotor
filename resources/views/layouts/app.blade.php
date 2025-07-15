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
            background-color: #0c2843;
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
       .pagination {
        --bs-pagination-padding-x: 0.5rem;
        --bs-pagination-padding-y: 0.25rem;
        --bs-pagination-font-size: 0.8rem;
        --bs-pagination-color: #6c757d;
        --bs-pagination-bg: #fff;
        --bs-pagination-border-width: 1px;
        --bs-pagination-border-color: #dee2e6;
        gap: 0.25rem;
    }
    .page-link {
        min-width: 30px;
        text-align: center;
        border-radius: 4px !important;
    }
    .pagination-sm .page-link {
        padding: 0.2rem 0.4rem;
        font-size: 0.75rem;
    }
           .product-search-results {
        position: relative;
        margin-top: 2px;
    }
    
    .product-search-results ul {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        list-style: none;
        padding: 0;
        margin: 0;
        background: white;
    }
    
    .product-search-results li {
        padding: 8px 12px;
        cursor: pointer;
    }
    
    .product-search-results li:hover {
        background-color: #f8f9fa;
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
                                    <a href="#"
                                        class="d-flex align-items-center text-decoration-none dropdown-toggle"
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

                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>

                                        @if (Route::has('logout'))
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                    <i class="fas fa-sign-out-alt me-2"></i> Keluar
                                                </a>
                                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                                    class="d-none">
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

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
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
    {{-- Select2 CSS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>



    @yield('scripts')
    @stack('scripts')
</body>

</html>

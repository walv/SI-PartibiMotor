<div class="d-flex flex-column flex-shrink-0 p-3">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">SI_PARTIBIMOTOR</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}"
                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-white' }}">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </a>
        </li>
        
        @if (auth()->check() && auth()->user()->role == 'admin')
            <li class="nav-item">
                <a href="{{ route('categories.index') }}"
                    class="nav-link {{ request()->routeIs('categories.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-tags me-2"></i>
                    Manajemen Kategori
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('products.index') }}"
                    class="nav-link {{ request()->routeIs('products.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-box me-2"></i>
                    Manajemen Produk
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('services.index') }}"
                    class="nav-link {{ request()->routeIs('services.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-tools me-2"></i>
                    Manajemen Jasa
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('purchases.index') }}"
                    class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-truck me-2"></i>
                    Transaksi Pembelian
                </a>
            </li>
        @endif
        
        <li class="nav-item">
            <a href="{{ route('sales.index') }}"
                class="nav-link {{ request()->routeIs('sales.*') ? 'active' : 'text-white' }}">
                <i class="fas fa-shopping-cart me-2"></i>
                Transaksi Penjualan
            </a>
        </li>

        @if (auth()->check() && auth()->user()->role == 'admin')
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-bs-toggle="collapse" data-bs-target="#reportMenu">
                    <i class="fas fa-file-alt me-2"></i>
                    Manajemen Laporan
                </a>
                <div id="reportMenu" class="collapse ps-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('reports.sales') }}" 
                               class="nav-link text-white {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                                Laporan Penjualan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.purchases') }}" 
                               class="nav-link text-white {{ request()->routeIs('reports.purchases') ? 'active' : '' }}">
                                Laporan Pembelian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.inventory') }}" 
                               class="nav-link text-white {{ request()->routeIs('reports.inventory') ? 'active' : '' }}">
                                Laporan Stok Barang
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.financial') }}" 
                               class="nav-link text-white {{ request()->routeIs('reports.financial') ? 'active' : '' }}">
                                Laporan Keuangan
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('forecast.index') }}"
                    class="nav-link {{ request()->routeIs('forecast.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-chart-line me-2"></i>
                    Peramalan
                </a>
            </li>
        @endif

        @if (auth()->check() && auth()->user()->role == 'admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->is('users*') ? 'active' : 'text-white' }}" 
                   href="{{ route('users.index') }}">
                    <i class="fas fa-users me-2"></i> 
                    Manajemen User
                </a>
            </li>
        @endif
        
        <li class="nav-item">
            <a class="nav-link text-white" href="{{ route('account.show') }}">
                <i class="fas fa-user-circle me-2"></i> 
                Akun Saya
            </a>
        </li>
        
        @if (auth()->check() && auth()->user()->role == 'admin')
            <li class="nav-item">
                <a href="{{ route('sales.exportimport') }}"
                    class="nav-link {{ request()->routeIs('sales.exportimport') ? 'active' : 'text-white' }}">
                    <i class="fas fa-file-export me-2"></i>
                    Export & Import data penjualan
                </a>
            </li>
        @endif
    </ul>
</div>
<div class="d-flex flex-column flex-shrink-0 p-3">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">SI_PARTIBIMOTOR</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-white' }}">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </a>
        </li>
        @if(auth()->check() && auth()->user()->role == 'admin')
        <li>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : 'text-white' }}">
                <i class="fas fa-tags me-2"></i>
                Manajemen Kategori
            </a>
        </li>
        <li>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : 'text-white' }}">
                <i class="fas fa-box me-2"></i>
                Manajemen Produk
            </a>
        </li>
        <li>
            <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : 'text-white' }}">
                <i class="fas fa-truck me-2"></i>
                Transaksi Pembelian
            </a>
        </li>
        <li>
            <a href="{{ route('forecast.index') }}" class="nav-link {{ request()->routeIs('forecast.*') ? 'active' : 'text-white' }}">
                <i class="fas fa-chart-line me-2"></i>
                Peramalan
            </a>
        </li>
        @endif
        <li>
            <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : 'text-white' }}">
                <i class="fas fa-shopping-cart me-2"></i>
                Transaksi Penjualan
            </a>
        </li>
    </ul>
</div>

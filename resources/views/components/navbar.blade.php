<nav class="navbar navbar-expand-lg navbar-light fixed-top modern-navbar shadow-sm">
  <div class="container-fluid px-4">
    <!-- Brand -->
    <a class="navbar-brand fw-bold fs-3" href="/beranda#">
      <i class="fas fa-fish text-primary"></i>
      Mancingin<span class="text-primary">Aje</span>
    </a>

    <!-- Search Bar (Desktop) -->
    <div class="d-none d-lg-flex flex-grow-1 mx-5">
      <form class="search-form w-100" action="{{ route('pages.orders') }}" method="GET">
        <div class="input-group modern-search">
          <span class="input-group-text bg-white border-end-0">
            <i class="fas fa-search text-muted"></i>
          </span>
          <input 
            class="form-control border-start-0 ps-0" 
            type="search" 
            name="search" 
            placeholder="Cari alat pancing, umpan, dan aksesori..."
            value="{{ request('search') }}"
          >
        </div>
      </form>
    </div>

    <!-- Toggler (Mobile) -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Right Menu -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Search Mobile -->
      <div class="d-lg-none my-3">
        <form action="{{ route('pages.orders') }}" method="GET">
          <div class="input-group modern-search">
            <input class="form-control" type="search" name="search" placeholder="Cari produk..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
          </div>
        </form>
      </div>

      <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
        <li class="nav-item">
          <a class="nav-link px-3" href="/"><i class="fas fa-home me-1"></i> Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3" href="/beranda/orders"><i class="fas fa-shopping-bag me-1"></i> Produk</a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3" href="#contact"><i class="fas fa-envelope me-1"></i> Kontak</a>
        </li>

        <li class="nav-item ms-lg-3">
          @guest
            <a href="/login" class="btn btn-outline-primary rounded-pill px-4">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk
            </a>
          @endguest

          @auth
            <div class="dropdown">
              <button class="btn btn-primary dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                <i class="fa-regular fa-user me-1"></i>{{ session('user_name') }}
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                <li><a class="dropdown-item" href="/beranda/yourorders">
                  <i class="fas fa-box me-2 text-primary"></i>Pesanan Saya
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                      <i class="fa-solid fa-right-from-bracket me-2"></i>Keluar
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          @endauth
        </li>

        <!-- Cart Icon -->
        <li class="nav-item ms-2">
          <a href="/beranda/cart" class="btn btn-light position-relative rounded-circle p-2" style="width: 45px; height: 45px;">
            <i class="fas fa-shopping-cart text-primary fs-5"></i>
            @php
                $cartCount = session('cart') ? count(session('cart')) : 0;
            @endphp
            @if($cartCount > 0)
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $cartCount }}
              </span>
            @endif
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
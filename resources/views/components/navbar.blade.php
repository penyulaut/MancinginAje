<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm p-3">
  <div class="container">

    <!-- Brand -->
    <a class="navbar-brand fw-bold fs-4" href="/beranda#">
      Kenangan <span class="text-warning">Senja</span>
    </a>

    <!-- Toggler (Mobile) -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
      aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu -->
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link px-3 active" href="/beranda#">Home</a></li>
        <li class="nav-item"><a class="nav-link px-3" href="/beranda#about">About</a></li>
        <li class="nav-item"><a class="nav-link px-3" href="/beranda#menu">Menu</a></li>
        <li class="nav-item"><a class="nav-link px-3" href="/beranda#contact">Contact</a></li>
        <li class="nav-item"><a class="nav-link px-3" href="/beranda/orders">Menu</a></li>

        <!-- Divider kecil -->
        <li><span class="text-secondary mx-2">|</span></li>

        <!-- Tombol Login -->
        <li class="nav-item">

            {{-- Jika BELUM login --}}
            @guest
                <a href="/login" class="btn btn-outline-warning rounded-pill px-3 py-1 fw-semibold">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                </a>
            @endguest

            {{-- Jika SUDAH login --}}
            @auth
            <div class="btn-group">
              <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-regular fa-user me-1"></i>{{ Auth::user()->name }}
              </button>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="dropdown-item">
                      <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                  </button>   
                  <li><a class="dropdown-item" href="/beranda/yourorders"><i class="fa-solid fa-bowl-food me-2"></i>Lihat Pesanan</a></li>             
                </form>                
              </ul>
            </div>           
            @endauth
        </li>


        <!-- Icon Cart -->
        <li class="nav-item position-relative ms-3">
          <a href="/beranda/cart" class="nav-link position-relative">
            <i class="fa-solid fa-cart-shopping text-light fa-lg"></i>
            @php
                $cartCount = session('cart') ? count(session('cart')) : 0;
            @endphp
            @if($cartCount > 0)
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                {{ $cartCount }}
              </span>
            @endif
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
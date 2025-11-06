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
        <li class="nav-item"><a class="nav-link px-3" href="/beranda/orders">Orders</a></li>

        <!-- Divider kecil -->
        <li><span class="text-secondary mx-2">|</span></li>

        <!-- Tombol Login -->
        <li class="nav-item">
          <a href="/login" class="btn btn-outline-warning rounded-pill px-3 py-1 fw-semibold">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Login
          </a>
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
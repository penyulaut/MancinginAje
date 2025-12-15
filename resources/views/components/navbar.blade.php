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
                <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                  <i class="fas fa-user-circle me-2 text-primary"></i>Profil Saya
                </a></li>
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

{{-- Search Autocomplete Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('input[name="search"]');
    let currentFocus = -1;
    let suggestionsContainer = null;

    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function(e) {
            const query = e.target.value.trim();
            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            fetch(`/api/search-suggestions?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    showSuggestions(data, e.target);
                })
                .catch(error => {
                    console.error('Search suggestions error:', error);
                    hideSuggestions();
                });
        }, 300));

        input.addEventListener('keydown', function(e) {
            if (!suggestionsContainer) return;

            const items = suggestionsContainer.querySelectorAll('.suggestion-item');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentFocus = currentFocus < items.length - 1 ? currentFocus + 1 : 0;
                highlightSuggestion(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentFocus = currentFocus > 0 ? currentFocus - 1 : items.length - 1;
                highlightSuggestion(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentFocus >= 0 && items[currentFocus]) {
                    items[currentFocus].click();
                } else {
                    input.closest('form').submit();
                }
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        });

        input.addEventListener('blur', function() {
            // Delay hiding to allow click on suggestions
            setTimeout(hideSuggestions, 150);
        });
    });

    function showSuggestions(suggestions, inputElement) {
        hideSuggestions();

        if (suggestions.length === 0) return;

        suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions shadow-lg';
        suggestionsContainer.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 0 0 12px 12px;
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
        `;

        suggestions.forEach((suggestion, index) => {
            const item = document.createElement('div');
            item.className = 'suggestion-item px-3 py-2 d-flex align-items-center';
            item.style.cssText = `
                cursor: pointer;
                border-bottom: 1px solid #f0f0f0;
                transition: background-color 0.2s;
            `;
            item.innerHTML = `
                <i class="fas ${getIconForType(suggestion.type)} me-2 text-primary"></i>
                <span class="flex-grow-1">${highlightMatch(suggestion.text, inputElement.value)}</span>
                <small class="text-muted">${getTypeLabel(suggestion.type)}</small>
            `;

            item.addEventListener('click', function() {
                window.location.href = suggestion.url;
            });

            item.addEventListener('mouseenter', function() {
                currentFocus = index;
                highlightSuggestion(suggestionsContainer.querySelectorAll('.suggestion-item'));
            });

            suggestionsContainer.appendChild(item);
        });

        // Position relative to input
        const inputWrapper = inputElement.closest('.input-group, .modern-search, .modern-search-lg');
        if (inputWrapper) {
            inputWrapper.style.position = 'relative';
            inputWrapper.appendChild(suggestionsContainer);
        }
    }

    function hideSuggestions() {
        if (suggestionsContainer) {
            suggestionsContainer.remove();
            suggestionsContainer = null;
            currentFocus = -1;
        }
    }

    function highlightSuggestion(items) {
        items.forEach((item, index) => {
            if (index === currentFocus) {
                item.style.backgroundColor = '#f8f9fa';
            } else {
                item.style.backgroundColor = 'white';
            }
        });
    }

    function highlightMatch(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark class="bg-warning">$1</mark>');
    }

    function getIconForType(type) {
        switch (type) {
            case 'product': return 'fa-box';
            case 'category': return 'fa-tags';
            case 'suggestion': return 'fa-search';
            default: return 'fa-search';
        }
    }

    function getTypeLabel(type) {
        switch (type) {
            case 'product': return 'Produk';
            case 'category': return 'Kategori';
            case 'suggestion': return 'Saran';
            default: return '';
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
</script>
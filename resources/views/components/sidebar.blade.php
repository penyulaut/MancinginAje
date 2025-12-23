  <style>   
    .sidebar {
      background-color: #212529;
      color: #fff;
      position: fixed;
      width: 220px;
      top: var(--navbar-height);
      left: 0;
      padding: 20px;
      height: calc(100vh - var(--navbar-height));
      overflow-y: auto;
    }
    .sidebar h4 {
      color: #ffc107;
      text-align: center;
      margin-bottom: 30px;
    }
    .sidebar a {
      display: block;
      color: #fff;
      text-decoration: none;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 5px;
    }
    .sidebar a:hover {
      background-color: #343a40;
    }
    .content {
      margin-left: 220px;
      padding: 20px;
      padding-bottom: 120px; /* prevent footer overlap */
    }
    .navbar-custom {
      background-color: #fff;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      padding: 10px 20px;
      border-radius: 10px;
    }
    .table-container {
      background-color: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
  </style>

  <!-- Sidebar -->
  <div class="sidebar">
    @php $base = route('admin.dashboard.index'); $tab = request('tab', 'products'); @endphp
    <a href="{{ route('admin.dashboard.index', ['tab' => 'products']) }}" class="{{ $tab==='products' ? 'active' : '' }}">Data Produk</a>
    <a href="{{ route('admin.dashboard.index', ['tab' => 'transactions']) }}" class="{{ $tab==='transactions' ? 'active' : '' }}">Data Transaksi</a>
    <a href="{{ route('admin.dashboard.index', ['tab' => 'users']) }}" class="{{ $tab==='users' ? 'active' : '' }}">Pengguna</a>
    <a href="{{ route('admin.dashboard.index', ['tab' => 'reports']) }}" class="{{ $tab==='reports' ? 'active' : '' }}">Laporan</a>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none">@csrf</form>
    <a href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a>
  </div>

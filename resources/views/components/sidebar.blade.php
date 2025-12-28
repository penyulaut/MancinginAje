<style>
  .admin-sidebar {
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    color: #fff;
    position: fixed;
    width: 250px;
    top: var(--navbar-height, 60px);
    left: 0;
    padding: 20px 15px;
    height: calc(100vh - var(--navbar-height, 60px));
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    z-index: 1000;
  }
  .admin-sidebar .sidebar-brand {
    color: #10b981;
    font-weight: 700;
    font-size: 1.25rem;
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
  }
  .admin-sidebar .sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .admin-sidebar .sidebar-menu li {
    margin-bottom: 5px;
  }
  .admin-sidebar .sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #94a3b8;
    text-decoration: none;
    padding: 12px 15px;
    border-radius: 10px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
  }
  .admin-sidebar .sidebar-menu a:hover {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
  }
  .admin-sidebar .sidebar-menu a.active {
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
  }
  .admin-sidebar .sidebar-menu a i {
    width: 20px;
    text-align: center;
  }
  .admin-sidebar .sidebar-divider {
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 20px 0;
  }
  .admin-sidebar .sidebar-section-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #64748b;
    padding: 0 15px;
    margin-bottom: 10px;
    letter-spacing: 0.5px;
  }

  /* Legacy support - keep old sidebar styles for non-admin pages */
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
    padding-bottom: 120px;
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

@php
  $currentRoute = Route::currentRouteName();
  $tab = request('tab', 'dashboard');
  
  // Determine active state for each menu item
  $isDashboard = $currentRoute === 'admin.dashboard.index' && $tab === 'dashboard';
  $isProducts = $currentRoute === 'admin.products.index' || ($currentRoute === 'admin.dashboard.index' && $tab === 'products');
  $isCategories = str_starts_with($currentRoute, 'admin.categories');
  $isTransactions = $currentRoute === 'admin.dashboard.index' && $tab === 'transactions';
  $isOrders = $currentRoute === 'admin.orders.index';
  $isUsers = $currentRoute === 'admin.dashboard.index' && $tab === 'users';
  $isReports = $currentRoute === 'admin.reports.index' || ($currentRoute === 'admin.dashboard.index' && $tab === 'reports');
@endphp

<!-- Admin Sidebar -->
<div class="admin-sidebar">
  <a href="/" class="sidebar-brand text-decoration-none">
    <i class="fas fa-fish me-2"></i>MancinginAje
  </a>
  
  <ul class="sidebar-menu">
    <li>
      <a href="{{ route('admin.dashboard.index', ['tab' => 'dashboard']) }}" class="{{ $isDashboard ? 'active' : '' }}">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </a>
    </li>
    
    <div class="sidebar-divider"></div>
    <div class="sidebar-section-title">Manage</div>
    
    <li>
      <a href="{{ route('admin.dashboard.index', ['tab' => 'products']) }}" class="{{ $isProducts ? 'active' : '' }}">
        <i class="fas fa-box"></i>
        <span>Products</span>
      </a>
    </li>
    <li>
      <a href="{{ route('admin.categories.index') }}" class="{{ $isCategories ? 'active' : '' }}">
        <i class="fas fa-tags"></i>
        <span>Categories</span>
      </a>
    </li>
    
    <div class="sidebar-divider"></div>
    <div class="sidebar-section-title">Sales</div>
    
    <li>
      <a href="{{ route('admin.dashboard.index', ['tab' => 'transactions']) }}" class="{{ $isTransactions ? 'active' : '' }}">
        <i class="fas fa-receipt"></i>
        <span>Transactions</span>
      </a>
    </li>
    <li>
      <a href="{{ route('admin.orders.index') }}" class="{{ $isOrders ? 'active' : '' }}">
        <i class="fas fa-shopping-cart"></i>
        <span>Orders</span>
      </a>
    </li>
    
    <div class="sidebar-divider"></div>
    <div class="sidebar-section-title">System</div>
    
    <li>
      <a href="{{ route('admin.dashboard.index', ['tab' => 'users']) }}" class="{{ $isUsers ? 'active' : '' }}">
        <i class="fas fa-users"></i>
        <span>Users</span>
      </a>
    </li>
    <li>
      <a href="{{ route('admin.reports.index') }}" class="{{ $isReports ? 'active' : '' }}">
        <i class="fas fa-chart-bar"></i>
        <span>Reports</span>
      </a>
    </li>
    
    <div class="sidebar-divider"></div>
    
    <li>
      <a href="{{ route('pages.beranda') }}">
        <i class="fas fa-store"></i>
        <span>View Store</span>
      </a>
    </li>
    <li>
      <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none">@csrf</form>
      <a href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </a>
    </li>
  </ul>
</div>

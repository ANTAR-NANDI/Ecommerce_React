<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EBay Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="admin-shell d-lg-flex">
    <aside class="sidebar d-flex flex-column" aria-label="Admin navigation">
        <div class="d-flex align-items-center gap-2 px-4 py-4">
            <div class="brand-mark"><i class="bi bi-bag-heart-fill"></i></div>
            <div class="brand-name">EBay</div>
        </div>
        <nav class="sidebar-nav flex-grow-1 overflow-auto pb-4">
            <p class="nav-label">Workspace</p>
            <a href="{{ route('admin.dashboard') }}" class="side-link active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            <a href="{{ route('admin.orders.index') }}" class="side-link"><i class="bi bi-receipt"></i> Ecommerce Orders</a>
            <a href="{{ route('admin.pos.index') }}" class="side-link"><i class="bi bi-shop-window"></i> POS</a>
            <a href="{{ route('admin.products.index') }}" class="side-link"><i class="bi bi-box-seam"></i> Products</a>
            @if(auth()->user()->isSuperAdmin())
            <button class="side-link border-0 w-100 text-start" type="button" data-admin-collapse="#dashboard-variant-menu" aria-expanded="false"><i class="bi bi-boxes"></i> Product Variant <i class="bi bi-chevron-down"></i></button>
            <div id="dashboard-variant-menu" class="collapse"><div class="ms-4 border-start" style="border-color:var(--line)!important"><a href="{{ route('admin.brands.index') }}" class="side-link py-2">Brand</a><a href="{{ route('admin.colors.index') }}" class="side-link py-2">Color</a><a href="{{ route('admin.sizes.index') }}" class="side-link py-2">Size</a><a href="{{ route('admin.units.index') }}" class="side-link py-2">Unit</a></div></div>
            <button class="side-link border-0 w-100 text-start" type="button" data-admin-collapse="#dashboard-category-menu" aria-expanded="false"><i class="bi bi-grid-3x3-gap-fill"></i> Category <i class="bi bi-chevron-down"></i></button>
            <div id="dashboard-category-menu" class="collapse">
            <div class="ms-4 border-start" style="border-color:var(--line)!important">
                <a href="{{ route('admin.categories.index') }}" class="side-link py-2">All category</a>
                <a href="{{ route('admin.categories.create') }}" class="side-link py-2">Add category</a>
                <a href="{{ route('admin.subcategories.index') }}" class="side-link py-2">All subcategory</a>
                <a href="{{ route('admin.subcategories.create') }}" class="side-link py-2">Add subcategory</a>
            </div>
            </div>
            <a href="{{ route('admin.customers.index') }}" class="side-link"><i class="bi bi-people"></i> Customers</a>
            <a href="{{ route('admin.blogs.index') }}" class="side-link"><i class="bi bi-journal-richtext"></i> Blog</a>
            <p class="nav-label">Operations</p>
            <a href="{{ route('admin.warehouses.index') }}" class="side-link"><i class="bi bi-buildings"></i> Warehouses</a>
            <a href="{{ route('admin.purchases.index') }}" class="side-link"><i class="bi bi-cart-plus"></i> Purchases</a>
            <a href="{{ route('admin.suppliers.index') }}" class="side-link"><i class="bi bi-truck"></i> Suppliers</a>
            <p class="nav-label">System</p>
            <a href="{{ route('admin.contact.edit') }}" class="side-link"><i class="bi bi-chat-dots"></i> Contact Us</a>
            <a href="{{ route('admin.media.index') }}" class="side-link"><i class="bi bi-images"></i> Media library</a>
            <a href="{{ route('admin.users.index') }}" class="side-link"><i class="bi bi-shield-lock"></i> Users & roles</a>
            @endif
        </nav>
        <div class="m-3 p-3 rounded-3" style="background:var(--brand-soft)">
            <div class="small fw-bold mb-1">Need assistance?</div>
            <div class="small" style="color:var(--muted)">Visit the help center</div>
        </div>
    </aside>
    <main class="flex-grow-1 min-vw-0">
        <header class="topbar px-3 px-lg-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="icon-button" data-sidebar-toggle aria-label="Toggle menu"><i class="bi bi-list fs-4"></i></button>
                <div><div class="topbar-title">Good morning, {{ Str::before($user->name, ' ') }}</div><div class="topbar-subtitle">Here’s what’s happening with your store today.</div></div>
            </div>
            <div class="d-flex align-items-center gap-1 gap-md-2">
                <a class="icon-button d-none d-sm-grid" href="{{ route('admin.products.index') }}" aria-label="Search products"><i class="bi bi-search"></i></a>
                <button class="icon-button" type="button" data-admin-theme="midnight" aria-label="Toggle appearance"><i class="bi bi-moon-stars"></i></button>
                <a class="icon-button d-grid" href="{{ route('admin.orders.index',['status'=>'pending']) }}" aria-label="Pending order notifications"><i class="bi bi-bell"></i><span class="notification-dot">{{ $statuses['pending'] }}</span></a>
                <div class="vr mx-1 d-none d-md-block"></div>
                <a href="{{ route('admin.profile.edit') }}" class="text-end d-none d-md-block me-1 text-decoration-none text-reset"><div class="small fw-semibold">{{ $user->name }}</div><div class="topbar-subtitle">{{ str_replace('_',' ',$user->role) }}</div></a>
                <a href="{{ route('admin.profile.edit') }}" class="avatar"><i class="bi bi-person-fill"></i></a>
            </div>
        </header>
        <div class="page-content">
            <section class="welcome-card d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div><p class="text-uppercase small fw-bold mb-1" style="color:var(--brand);letter-spacing:.08em">Store overview</p><h1 class="mb-1">Your store is growing beautifully.</h1><p class="mb-0 small" style="color:var(--muted)">Sales are up 18.6% compared with last month.</p></div>
                <a href="{{ route('admin.products.create') }}" class="btn px-3 py-2 text-white" style="background:var(--brand);border-radius:10px"><i class="bi bi-plus-lg me-1"></i> Add product</a>
            </section>
            <section class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3"><a href="{{ route('admin.warehouses.index') }}" class="stat-card d-block text-reset text-decoration-none" style="--accent:#7256e7;--tint:#f0edff"><div class="stat-icon"><i class="bi bi-shop"></i></div><div class="stat-number">{{ number_format($metrics['warehouses']) }}</div><div class="stat-name">Active warehouses</div></a></div>
                <div class="col-sm-6 col-xl-3"><a href="{{ route('admin.products.index') }}" class="stat-card d-block text-reset text-decoration-none" style="--accent:#1677d2;--tint:#e8f3ff"><div class="stat-icon"><i class="bi bi-box-seam"></i></div><div class="stat-number">{{ number_format($metrics['products']) }}</div><div class="stat-name">Products in catalogue</div></a></div>
                <div class="col-sm-6 col-xl-3"><a href="{{ route('admin.categories.index') }}" class="stat-card d-block text-reset text-decoration-none" style="--accent:#dc4e68;--tint:#ffedf0"><div class="stat-icon"><i class="bi bi-grid-3x3-gap"></i></div><div class="stat-number">{{ number_format($metrics['categories']) }}</div><div class="stat-name">Active categories</div></a></div>
                <div class="col-sm-6 col-xl-3"><a href="{{ route('admin.orders.index') }}" class="stat-card d-block text-reset text-decoration-none" style="--accent:#208a50;--tint:#eaf9ef"><div class="stat-icon"><i class="bi bi-bag-check"></i></div><div class="stat-number">{{ number_format($metrics['orders_month']) }}</div><div class="stat-name">Orders this month</div></a></div>
            </section>
            <section class="panel mb-4"><div class="d-flex align-items-center justify-content-between mb-3"><div class="panel-heading">Order pipeline</div><a href="{{ route('admin.orders.index') }}" class="small text-decoration-none" style="color:var(--brand)">View all orders <i class="bi bi-arrow-right"></i></a></div><div class="d-flex gap-2 overflow-auto pb-1">
                @foreach ([['pending','Pending','clock'],['accepted','Accepted','check2-circle'],['processing','Processing','box'],['shipped','Shipped','truck'],['delivered','Delivered','patch-check'],['cancelled','Cancelled','x-circle']] as [$status,$label,$icon])
                    <a href="{{ route('admin.orders.index',['status'=>$status]) }}" class="status-tile flex-shrink-0 text-decoration-none"><small><i class="bi bi-{{ $icon }} me-1"></i>{{ $label }}</small><strong>{{ $statuses[$status] }}</strong></a>
                @endforeach
            </div></section>
            <section class="row g-4"><div class="col-lg-8"><div class="panel h-100"><div class="d-flex justify-content-between align-items-center"><div><div class="panel-heading">Revenue performance</div><div class="small mt-1" style="color:var(--muted)">Delivered orders this week</div></div><a href="{{ route('admin.orders.index',['status'=>'delivered']) }}" class="btn btn-sm border" style="border-color:var(--line)!important;color:var(--muted)">View delivered orders <i class="bi bi-arrow-right ms-1"></i></a></div><div class="d-flex align-items-end gap-3 mt-3"><div class="revenue-value">${{ number_format($metrics['revenue'],2) }}</div></div><p class="small mt-3 mb-0" style="color:var(--muted)">Revenue updates from delivered ecommerce orders.</p></div></div>
            <div class="col-lg-4"><div class="panel h-100"><div class="panel-heading mb-1">Admin appearance</div><p class="small mb-3" style="color:var(--muted)">Choose a saved theme for this dashboard.</p><div class="vstack gap-2"><button class="theme-option text-start" data-admin-theme="light"><span class="theme-dot me-2" style="background:#7256e7"></span><strong class="small">Light</strong><span class="small float-end" style="color:var(--muted)">Default</span></button><button class="theme-option text-start" data-admin-theme="midnight"><span class="theme-dot me-2" style="background:#8d7cff"></span><strong class="small">Midnight</strong></button><button class="theme-option text-start" data-admin-theme="royal"><span class="theme-dot me-2" style="background:#be8a22"></span><strong class="small">Royal</strong></button><button class="theme-option text-start" data-admin-theme="forest"><span class="theme-dot me-2" style="background:#1c8d68"></span><strong class="small">Forest</strong></button></div></div></div></section>
        </div>
    </main>
</div>
</body>
</html>

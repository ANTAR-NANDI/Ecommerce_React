<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Velora Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="admin-shell d-lg-flex">
    <aside class="sidebar d-flex flex-column" aria-label="Admin navigation">
        <div class="d-flex align-items-center gap-2 px-4 py-4">
            <div class="brand-mark"><i class="bi bi-bag-heart-fill"></i></div>
            <div class="brand-name">Velora<span>Commerce</span></div>
        </div>
        <nav class="flex-grow-1 overflow-auto pb-4">
            <p class="nav-label">Workspace</p>
            <a href="{{ route('admin.dashboard') }}" class="side-link active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            <a href="#" class="side-link"><i class="bi bi-receipt"></i> Orders <span class="badge rounded-pill ms-auto" style="background:var(--brand-soft);color:var(--brand)">12</span></a>
            <a href="#" class="side-link"><i class="bi bi-box-seam"></i> Products <i class="bi bi-chevron-down"></i></a>
            <a href="#" class="side-link"><i class="bi bi-tags"></i> Categories</a>
            <a href="#" class="side-link"><i class="bi bi-people"></i> Customers</a>
            <p class="nav-label">Operations</p>
            <a href="#" class="side-link"><i class="bi bi-truck"></i> Delivery</a>
            <a href="#" class="side-link"><i class="bi bi-arrow-counterclockwise"></i> Returns</a>
            <a href="#" class="side-link"><i class="bi bi-megaphone"></i> Promotions</a>
            <p class="nav-label">System</p>
            <a href="#" class="side-link"><i class="bi bi-palette"></i> Store themes</a>
            <a href="#" class="side-link"><i class="bi bi-gear"></i> Settings</a>
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
                <div><div class="topbar-title">Good morning, Olivia</div><div class="topbar-subtitle">Here’s what’s happening with your store today.</div></div>
            </div>
            <div class="d-flex align-items-center gap-1 gap-md-2">
                <button class="icon-button d-none d-sm-block" aria-label="Search"><i class="bi bi-search"></i></button>
                <button class="icon-button" aria-label="Toggle appearance"><i class="bi bi-moon-stars"></i></button>
                <button class="icon-button" aria-label="Notifications"><i class="bi bi-bell"></i><span class="notification-dot">3</span></button>
                <div class="vr mx-1 d-none d-md-block"></div>
                <div class="text-end d-none d-md-block me-1"><div class="small fw-semibold">Olivia Carter</div><div class="topbar-subtitle">Super Admin</div></div>
                <div class="avatar"><i class="bi bi-person-fill"></i></div>
            </div>
        </header>
        <div class="page-content">
            <section class="welcome-card d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div><p class="text-uppercase small fw-bold mb-1" style="color:var(--brand);letter-spacing:.08em">Store overview</p><h1 class="mb-1">Your store is growing beautifully.</h1><p class="mb-0 small" style="color:var(--muted)">Sales are up 18.6% compared with last month.</p></div>
                <button class="btn px-3 py-2 text-white" style="background:var(--brand);border-radius:10px"><i class="bi bi-plus-lg me-1"></i> Add product</button>
            </section>
            <section class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3"><div class="stat-card" style="--accent:#7256e7;--tint:#f0edff"><div class="stat-icon"><i class="bi bi-shop"></i></div><div class="stat-number">24</div><div class="stat-name">Active shops <span class="text-success ms-1">+2</span></div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card" style="--accent:#1677d2;--tint:#e8f3ff"><div class="stat-icon"><i class="bi bi-box-seam"></i></div><div class="stat-number">1,248</div><div class="stat-name">Products in catalogue</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card" style="--accent:#dc4e68;--tint:#ffedf0"><div class="stat-icon"><i class="bi bi-bag-check"></i></div><div class="stat-number">186</div><div class="stat-name">Orders this month <span class="text-success ms-1">+12.4%</span></div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card" style="--accent:#208a50;--tint:#eaf9ef"><div class="stat-icon"><i class="bi bi-people"></i></div><div class="stat-number">864</div><div class="stat-name">Total customers</div></div></div>
            </section>
            <section class="panel mb-4"><div class="d-flex align-items-center justify-content-between mb-3"><div class="panel-heading">Order pipeline</div><a href="#" class="small text-decoration-none" style="color:var(--brand)">View all orders <i class="bi bi-arrow-right"></i></a></div><div class="d-flex gap-2 overflow-auto pb-1">
                @foreach ([['Pending',18,'clock'],['Confirmed',12,'check2-circle'],['Packing',9,'box'],['Shipped',16,'truck'],['Delivered',124,'patch-check'],['Cancelled',7,'x-circle']] as [$label,$value,$icon])
                    <div class="status-tile flex-shrink-0"><small><i class="bi bi-{{ $icon }} me-1"></i>{{ $label }}</small><strong>{{ $value }}</strong></div>
                @endforeach
            </div></section>
            <section class="row g-4"><div class="col-lg-8"><div class="panel h-100"><div class="d-flex justify-content-between align-items-center"><div><div class="panel-heading">Revenue performance</div><div class="small mt-1" style="color:var(--muted)">Last 7 days</div></div><button class="btn btn-sm border" style="border-color:var(--line)!important;color:var(--muted)">This week <i class="bi bi-chevron-down ms-1"></i></button></div><div class="d-flex align-items-end gap-3 mt-3"><div class="revenue-value">$12,840</div><span class="small text-success pb-2"><i class="bi bi-graph-up-arrow"></i> 18.6%</span></div><div class="chart-bars"><span style="height:42%"></span><span style="height:61%"></span><span style="height:50%"></span><span style="height:76%"></span><span style="height:59%"></span><span style="height:92%"></span><span style="height:82%"></span></div><div class="d-flex justify-content-between small pt-2" style="color:var(--muted)"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div></div></div>
            <div class="col-lg-4"><div class="panel h-100"><div class="panel-heading mb-1">Admin appearance</div><p class="small mb-3" style="color:var(--muted)">Choose a saved theme for this dashboard.</p><div class="vstack gap-2"><button class="theme-option text-start" data-admin-theme="light"><span class="theme-dot me-2" style="background:#7256e7"></span><strong class="small">Light</strong><span class="small float-end" style="color:var(--muted)">Default</span></button><button class="theme-option text-start" data-admin-theme="midnight"><span class="theme-dot me-2" style="background:#8d7cff"></span><strong class="small">Midnight</strong></button><button class="theme-option text-start" data-admin-theme="royal"><span class="theme-dot me-2" style="background:#be8a22"></span><strong class="small">Royal</strong></button><button class="theme-option text-start" data-admin-theme="forest"><span class="theme-dot me-2" style="background:#1c8d68"></span><strong class="small">Forest</strong></button></div></div></div></section>
        </div>
    </main>
</div>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Admin') · VeloraCommerce</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
@php($superAdmin = auth()->user()->isSuperAdmin())
<div class="admin-shell d-lg-flex">
    <aside class="sidebar d-flex flex-column" id="admin-sidebar">
        <div class="d-flex align-items-center gap-2 px-4 py-4">
            <div class="brand-mark"><i class="bi bi-bag-heart-fill"></i></div>
            <div class="brand-name">Velora<span>Commerce</span></div>
        </div>
        <nav class="sidebar-nav flex-grow-1 overflow-auto pb-4">
            <a href="{{ route('admin.dashboard') }}" class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            <a href="{{ route('admin.orders.index') }}" class="side-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><i class="bi bi-receipt-cutoff"></i> Ecommerce orders</a>

            @php($posOpen = request()->routeIs('admin.pos.*'))
            <button type="button" class="side-link border-0 w-100 text-start {{ $posOpen ? 'active' : '' }}" data-admin-collapse="#pos-menu" aria-expanded="{{ $posOpen ? 'true' : 'false' }}">
                <i class="bi bi-shop-window"></i> POS <i class="bi bi-chevron-down"></i>
            </button>
            <div id="pos-menu" class="collapse {{ $posOpen ? 'show' : '' }}"><div class="ms-4 border-start">
                <a href="{{ route('admin.pos.index') }}" class="side-link py-2">Point of sale</a>
                <a href="{{ route('admin.pos.history') }}" class="side-link py-2">Sales history</a>
                <a href="{{ route('admin.pos.drafts') }}" class="side-link py-2">POS drafts</a>
            </div></div>

            @unless($superAdmin)
                <a href="{{ route('admin.products.index') }}" class="side-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> Warehouse stock</a>
            @endunless

            @if($superAdmin)
                @php($productOpen = request()->routeIs('admin.products.*'))
                <button type="button" class="side-link border-0 w-100 text-start {{ $productOpen ? 'active' : '' }}" data-admin-collapse="#product-menu" aria-expanded="{{ $productOpen ? 'true' : 'false' }}"><i class="bi bi-box-seam"></i> Product <i class="bi bi-chevron-down"></i></button>
                <div id="product-menu" class="collapse {{ $productOpen ? 'show' : '' }}"><div class="ms-4 border-start">
                    <a href="{{ route('admin.products.index') }}" class="side-link py-2">All products</a>
                    <a href="{{ route('admin.products.create') }}" class="side-link py-2">Add product</a>
                </div></div>

                @php($categoryOpen = request()->routeIs('admin.categories.*') || request()->routeIs('admin.subcategories.*'))
                <button type="button" class="side-link border-0 w-100 text-start {{ $categoryOpen ? 'active' : '' }}" data-admin-collapse="#category-menu" aria-expanded="{{ $categoryOpen ? 'true' : 'false' }}"><i class="bi bi-grid-3x3-gap-fill"></i> Category <i class="bi bi-chevron-down"></i></button>
                <div id="category-menu" class="collapse {{ $categoryOpen ? 'show' : '' }}"><div class="ms-4 border-start">
                    <a href="{{ route('admin.categories.index') }}" class="side-link py-2">All category</a>
                    <a href="{{ route('admin.categories.create') }}" class="side-link py-2">Add category</a>
                    <a href="{{ route('admin.subcategories.index') }}" class="side-link py-2">All subcategory</a>
                    <a href="{{ route('admin.subcategories.create') }}" class="side-link py-2">Add subcategory</a>
                </div></div>

                @php($variantOpen = request()->routeIs('admin.brands.*') || request()->routeIs('admin.colors.*') || request()->routeIs('admin.sizes.*') || request()->routeIs('admin.units.*'))
                <button type="button" class="side-link border-0 w-100 text-start {{ $variantOpen ? 'active' : '' }}" data-admin-collapse="#variant-menu" aria-expanded="{{ $variantOpen ? 'true' : 'false' }}"><i class="bi bi-boxes"></i> Product Variant <i class="bi bi-chevron-down"></i></button>
                <div id="variant-menu" class="collapse {{ $variantOpen ? 'show' : '' }}"><div class="ms-4 border-start">
                    <a href="{{ route('admin.brands.index') }}" class="side-link py-2">Brand</a>
                    <a href="{{ route('admin.colors.index') }}" class="side-link py-2">Color</a>
                    <a href="{{ route('admin.sizes.index') }}" class="side-link py-2">Size</a>
                    <a href="{{ route('admin.units.index') }}" class="side-link py-2">Unit</a>
                </div></div>

                @php($promotionOpen = request()->routeIs('admin.promotions.*'))
                <button type="button" class="side-link border-0 w-100 text-start {{ $promotionOpen ? 'active' : '' }}" data-admin-collapse="#promotion-menu" aria-expanded="{{ $promotionOpen ? 'true' : 'false' }}"><i class="bi bi-megaphone-fill"></i> Promotion <i class="bi bi-chevron-down"></i></button>
                <div id="promotion-menu" class="collapse {{ $promotionOpen ? 'show' : '' }}"><div class="ms-4 border-start">
                    <a href="{{ route('admin.promotions.index', 'flash-deals') }}" class="side-link py-2 {{ request()->route('type') === 'flash-deals' ? 'active' : '' }}">Flash Deals</a>
                    <a href="{{ route('admin.promotions.index', 'banners') }}" class="side-link py-2 {{ request()->route('type') === 'banners' ? 'active' : '' }}">Banner Setup</a>
                    <a href="{{ route('admin.promotions.index', 'ads-campaigns') }}" class="side-link py-2 {{ request()->route('type') === 'ads-campaigns' ? 'active' : '' }}">Ads Campaign</a>
                    <a href="{{ route('admin.promotions.index', 'promo-codes') }}" class="side-link py-2 {{ request()->route('type') === 'promo-codes' ? 'active' : '' }}">Promo Code</a>
                </div></div>

                <a href="{{ route('admin.customers.index') }}" class="side-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i> Customers</a>
                <a href="{{ route('admin.blogs.index') }}" class="side-link {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}"><i class="bi bi-journal-richtext"></i> Blog</a>
                @php($cmsOpen = request()->routeIs('admin.cms.*'))
                <button type="button" class="side-link border-0 w-100 text-start {{ $cmsOpen ? 'active' : '' }}" data-admin-collapse="#cms-menu" aria-expanded="{{ $cmsOpen ? 'true' : 'false' }}"><i class="bi bi-window-stack"></i> CMS <i class="bi bi-chevron-down"></i></button>
                <div id="cms-menu" class="collapse {{ $cmsOpen ? 'show' : '' }}"><div class="ms-4 border-start">
                    <a href="{{ route('admin.cms.pages.index') }}" class="side-link py-2">Pages</a>
                    <a href="{{ route('admin.cms.menus.index') }}" class="side-link py-2">Menus</a>
                    <a href="{{ route('admin.cms.footer.edit') }}" class="side-link py-2">Footer</a>
                </div></div>
                <a href="{{ route('admin.contact.edit') }}" class="side-link {{ request()->routeIs('admin.contact.*') ? 'active' : '' }}"><i class="bi bi-chat-dots-fill"></i> Contact Us</a>

                <p class="nav-label">ERP</p>
                <a href="{{ route('admin.warehouses.index') }}" class="side-link {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}"><i class="bi bi-buildings"></i> Warehouses</a>
                <a href="{{ route('admin.purchases.index') }}" class="side-link {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}"><i class="bi bi-cart-plus"></i> Purchases</a>
                <a href="{{ route('admin.suppliers.index') }}" class="side-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}"><i class="bi bi-truck"></i> Suppliers</a>
                <a href="{{ route('admin.media.index') }}" class="side-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}"><i class="bi bi-images"></i> Media library</a>

                <p class="nav-label">Administration</p>
                <a href="{{ route('admin.users.index') }}" class="side-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-shield-lock-fill"></i> Users & roles</a>
            @endif
        </nav>
    </aside>
    <button class="sidebar-backdrop" type="button" aria-label="Close navigation" data-sidebar-close></button>

    <main class="flex-grow-1 min-vw-0">
        <header class="topbar px-3 px-lg-4 d-flex align-items-center">
            <button class="icon-button me-3" type="button" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false"><i class="bi bi-list fs-4"></i></button>
            <div><div class="topbar-title">Ecommerce & ERP</div><div class="topbar-subtitle">{{ $superAdmin ? 'Super Admin · all warehouses' : auth()->user()->warehouse?->name.' · '.str_replace('_',' ',auth()->user()->role) }}</div></div>
            <form method="POST" action="{{ route('logout') }}" class="ms-auto">@csrf<button class="btn btn-sm btn-outline-secondary">Sign out</button></form>
        </header>
        <div class="page-content">
            @if(session('success'))<div class="alert alert-success border-0 rounded-3 mb-4">{{ session('success') }}</div>@endif
            @yield('content')
        </div>
    </main>
</div>
<div class="modal fade" id="media-picker-modal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Choose from Media Library</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div id="media-picker-content" class="modal-body"></div></div></div></div>
</body>
</html>

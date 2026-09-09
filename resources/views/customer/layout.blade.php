<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'My Account') · EBay</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="customer-site-header">
        <div class="customer-site-topbar"><span>Free Delivery on orders over $999.00</span><span>Daily Deals &nbsp; · &nbsp; Easy Returns &nbsp; · &nbsp; Help Center</span><span>English &nbsp; · &nbsp; $ USD</span></div>
        <div class="customer-site-mainbar">
            <a class="customer-site-brand" href="/"><i class="bi bi-bag-heart-fill"></i><span>EBay</span></a>
            <form class="customer-site-search" action="/products" method="GET"><input name="search" placeholder="Search for products, brands and more..."><select name="category"><option>All Categories</option></select><button aria-label="Search"><i class="bi bi-search"></i></button></form>
            <div class="customer-site-actions"><a href="/account/wishlist"><i class="bi bi-heart"></i></a><a href="/checkout"><i class="bi bi-bag"></i></a><a class="customer-site-account" href="{{ route('customer.dashboard') }}"><i class="bi bi-person-circle"></i><span>{{ $customer->full_name }}<small>My Account</small></span></a></div>
        </div>
        <nav class="customer-site-nav"><a href="/products">Products</a><a href="/">Home</a><a href="/shops">Shops</a><a href="/brands">Brands</a><a href="/contact-us">Contact</a><a href="/blogs">Blogs</a></nav>
    </header>
    <div class="customer-shell">
        <aside class="customer-sidebar">
            <a class="customer-logo" href="/"><i class="bi bi-bag-heart-fill"></i><span>EBay</span></a>
            <div class="customer-profile">
                <div class="customer-avatar">
                    @if($customer->profileMedia)
                        <img src="{{ $customer->profileMedia->url }}" alt="{{ $customer->full_name }}">
                    @else
                        <i class="bi bi-person-fill"></i>
                    @endif
                </div>
                <b>{{ $customer->full_name }}</b>
                <small>{{ $customer->phone }}</small>
            </div>
            <nav class="customer-nav">
                <a class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}" href="{{ route('customer.dashboard') }}"><i class="bi bi-grid-1x2"></i>Dashboard</a>
                <a class="{{ request()->routeIs('customer.orders*') ? 'active' : '' }}" href="{{ route('customer.orders') }}"><i class="bi bi-box-seam"></i>Order History</a>
                <a class="{{ request()->routeIs('customer.wishlist') ? 'active' : '' }}" href="{{ route('customer.wishlist') }}"><i class="bi bi-heart"></i>Wishlist</a>
                <a class="{{ request()->routeIs('customer.profile') ? 'active' : '' }}" href="{{ route('customer.profile') }}"><i class="bi bi-person"></i>My Profile</a>
                <a class="{{ request()->routeIs('customer.addresses') ? 'active' : '' }}" href="{{ route('customer.addresses') }}"><i class="bi bi-geo-alt"></i>Manage Address</a>
                <a class="{{ request()->routeIs('customer.password') ? 'active' : '' }}" href="{{ route('customer.password') }}"><i class="bi bi-key"></i>Change Password</a>
            </nav>
        </aside>
        <main class="customer-main">
            <header class="customer-topbar">
                <h1>@yield('title', 'My Account')</h1>
                <div>
                    <a href="/">Continue shopping</a>
                    <form method="POST" action="{{ route('customer.logout') }}">
                        @csrf
                        <button>Log out <i class="bi bi-box-arrow-right"></i></button>
                    </form>
                </div>
            </header>
            <section class="customer-content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>

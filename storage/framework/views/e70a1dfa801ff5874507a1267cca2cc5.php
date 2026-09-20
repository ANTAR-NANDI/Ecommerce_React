<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title','Admin'); ?> · EBay</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>
</head>
<body>
<?php ($superAdmin = auth()->user()->isSuperAdmin()); ?>
<div class="admin-shell d-lg-flex">
    <aside class="sidebar d-flex flex-column" id="admin-sidebar">
        <div class="d-flex align-items-center gap-2 px-4 py-4">
            <div class="brand-mark"><i class="bi bi-bag-heart-fill"></i></div>
            <div class="brand-name">EBay</div>
        </div>
        <nav class="sidebar-nav flex-grow-1 overflow-auto pb-4">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="side-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            <a href="<?php echo e(route('admin.orders.index')); ?>" class="side-link <?php echo e(request()->routeIs('admin.orders.*') ? 'active' : ''); ?>"><i class="bi bi-receipt-cutoff"></i> Ecommerce orders</a>

            <?php ($posOpen = request()->routeIs('admin.pos.*')); ?>
            <button type="button" class="side-link border-0 w-100 text-start <?php echo e($posOpen ? 'active' : ''); ?>" data-admin-collapse="#pos-menu" aria-expanded="<?php echo e($posOpen ? 'true' : 'false'); ?>">
                <i class="bi bi-shop-window"></i> POS <i class="bi bi-chevron-down"></i>
            </button>
            <div id="pos-menu" class="collapse <?php echo e($posOpen ? 'show' : ''); ?>"><div class="ms-4 border-start">
                <a href="<?php echo e(route('admin.pos.index')); ?>" class="side-link py-2">Point of sale</a>
                <a href="<?php echo e(route('admin.pos.history')); ?>" class="side-link py-2">Sales history</a>
                <a href="<?php echo e(route('admin.pos.drafts')); ?>" class="side-link py-2">POS drafts</a>
            </div></div>

            <?php if (! ($superAdmin)): ?>
                <a href="<?php echo e(route('admin.products.index')); ?>" class="side-link <?php echo e(request()->routeIs('admin.products.*') ? 'active' : ''); ?>"><i class="bi bi-box-seam"></i> Warehouse stock</a>
            <?php endif; ?>

            <?php if($superAdmin): ?>
                <?php ($productOpen = request()->routeIs('admin.products.*')); ?>
                <button type="button" class="side-link border-0 w-100 text-start <?php echo e($productOpen ? 'active' : ''); ?>" data-admin-collapse="#product-menu" aria-expanded="<?php echo e($productOpen ? 'true' : 'false'); ?>"><i class="bi bi-box-seam"></i> Product <i class="bi bi-chevron-down"></i></button>
                <div id="product-menu" class="collapse <?php echo e($productOpen ? 'show' : ''); ?>"><div class="ms-4 border-start">
                    <a href="<?php echo e(route('admin.products.index')); ?>" class="side-link py-2">All products</a>
                    <a href="<?php echo e(route('admin.products.create')); ?>" class="side-link py-2">Add product</a>
                </div></div>

                <?php ($categoryOpen = request()->routeIs('admin.categories.*') || request()->routeIs('admin.subcategories.*')); ?>
                <button type="button" class="side-link border-0 w-100 text-start <?php echo e($categoryOpen ? 'active' : ''); ?>" data-admin-collapse="#category-menu" aria-expanded="<?php echo e($categoryOpen ? 'true' : 'false'); ?>"><i class="bi bi-grid-3x3-gap-fill"></i> Category <i class="bi bi-chevron-down"></i></button>
                <div id="category-menu" class="collapse <?php echo e($categoryOpen ? 'show' : ''); ?>"><div class="ms-4 border-start">
                    <a href="<?php echo e(route('admin.categories.index')); ?>" class="side-link py-2">All category</a>
                    <a href="<?php echo e(route('admin.categories.create')); ?>" class="side-link py-2">Add category</a>
                    <a href="<?php echo e(route('admin.subcategories.index')); ?>" class="side-link py-2">All subcategory</a>
                    <a href="<?php echo e(route('admin.subcategories.create')); ?>" class="side-link py-2">Add subcategory</a>
                </div></div>

                <?php ($variantOpen = request()->routeIs('admin.brands.*') || request()->routeIs('admin.colors.*') || request()->routeIs('admin.sizes.*') || request()->routeIs('admin.units.*')); ?>
                <button type="button" class="side-link border-0 w-100 text-start <?php echo e($variantOpen ? 'active' : ''); ?>" data-admin-collapse="#variant-menu" aria-expanded="<?php echo e($variantOpen ? 'true' : 'false'); ?>"><i class="bi bi-boxes"></i> Product Variant <i class="bi bi-chevron-down"></i></button>
                <div id="variant-menu" class="collapse <?php echo e($variantOpen ? 'show' : ''); ?>"><div class="ms-4 border-start">
                    <a href="<?php echo e(route('admin.brands.index')); ?>" class="side-link py-2">Brand</a>
                    <a href="<?php echo e(route('admin.colors.index')); ?>" class="side-link py-2">Color</a>
                    <a href="<?php echo e(route('admin.sizes.index')); ?>" class="side-link py-2">Size</a>
                    <a href="<?php echo e(route('admin.units.index')); ?>" class="side-link py-2">Unit</a>
                </div></div>

                <?php ($promotionOpen = request()->routeIs('admin.promotions.*')); ?>
                <button type="button" class="side-link border-0 w-100 text-start <?php echo e($promotionOpen ? 'active' : ''); ?>" data-admin-collapse="#promotion-menu" aria-expanded="<?php echo e($promotionOpen ? 'true' : 'false'); ?>"><i class="bi bi-megaphone-fill"></i> Promotion <i class="bi bi-chevron-down"></i></button>
                <div id="promotion-menu" class="collapse <?php echo e($promotionOpen ? 'show' : ''); ?>"><div class="ms-4 border-start">
                    <a href="<?php echo e(route('admin.promotions.index', 'flash-deals')); ?>" class="side-link py-2 <?php echo e(request()->route('type') === 'flash-deals' ? 'active' : ''); ?>">Flash Deals</a>
                    <a href="<?php echo e(route('admin.promotions.index', 'banners')); ?>" class="side-link py-2 <?php echo e(request()->route('type') === 'banners' ? 'active' : ''); ?>">Banner Setup</a>
                    <a href="<?php echo e(route('admin.promotions.index', 'ads-campaigns')); ?>" class="side-link py-2 <?php echo e(request()->route('type') === 'ads-campaigns' ? 'active' : ''); ?>">Ads Campaign</a>
                    <a href="<?php echo e(route('admin.promotions.index', 'promo-codes')); ?>" class="side-link py-2 <?php echo e(request()->route('type') === 'promo-codes' ? 'active' : ''); ?>">Promo Code</a>
                </div></div>

                <a href="<?php echo e(route('admin.customers.index')); ?>" class="side-link <?php echo e(request()->routeIs('admin.customers.*') ? 'active' : ''); ?>"><i class="bi bi-people-fill"></i> Customers</a>
                <a href="<?php echo e(route('admin.blogs.index')); ?>" class="side-link <?php echo e(request()->routeIs('admin.blogs.*') ? 'active' : ''); ?>"><i class="bi bi-journal-richtext"></i> Blog</a>
                <?php ($cmsOpen = request()->routeIs('admin.cms.*')); ?>
                <button type="button" class="side-link border-0 w-100 text-start <?php echo e($cmsOpen ? 'active' : ''); ?>" data-admin-collapse="#cms-menu" aria-expanded="<?php echo e($cmsOpen ? 'true' : 'false'); ?>"><i class="bi bi-window-stack"></i> CMS <i class="bi bi-chevron-down"></i></button>
                <div id="cms-menu" class="collapse <?php echo e($cmsOpen ? 'show' : ''); ?>"><div class="ms-4 border-start">
                    <a href="<?php echo e(route('admin.cms.pages.index')); ?>" class="side-link py-2">Pages</a>
                    <a href="<?php echo e(route('admin.cms.menus.index')); ?>" class="side-link py-2">Menus</a>
                    <a href="<?php echo e(route('admin.cms.footer.edit')); ?>" class="side-link py-2">Footer</a>
                </div></div>
                <a href="<?php echo e(route('admin.contact.edit')); ?>" class="side-link <?php echo e(request()->routeIs('admin.contact.*') ? 'active' : ''); ?>"><i class="bi bi-chat-dots-fill"></i> Contact Us</a>

                <p class="nav-label">ERP</p>
                <a href="<?php echo e(route('admin.warehouses.index')); ?>" class="side-link <?php echo e(request()->routeIs('admin.warehouses.*') ? 'active' : ''); ?>"><i class="bi bi-buildings"></i> Warehouses</a>
                <a href="<?php echo e(route('admin.purchases.index')); ?>" class="side-link <?php echo e(request()->routeIs('admin.purchases.*') ? 'active' : ''); ?>"><i class="bi bi-cart-plus"></i> Purchases</a>
                <a href="<?php echo e(route('admin.suppliers.index')); ?>" class="side-link <?php echo e(request()->routeIs('admin.suppliers.*') ? 'active' : ''); ?>"><i class="bi bi-truck"></i> Suppliers</a>
                <?php ($accountsOpen = request()->routeIs('admin.accounts.*') || request()->routeIs('admin.reports.*')); ?>
                <button type="button" class="side-link border-0 w-100 text-start <?php echo e($accountsOpen ? 'active' : ''); ?>" data-admin-collapse="#accounts-menu" aria-expanded="<?php echo e($accountsOpen ? 'true' : 'false'); ?>"><i class="bi bi-cash-stack"></i> Accounts <i class="bi bi-chevron-down"></i></button>
                <div id="accounts-menu" class="collapse <?php echo e($accountsOpen ? 'show' : ''); ?>"><div class="ms-4 border-start">
                    <a href="<?php echo e(route('admin.accounts.coa')); ?>" class="side-link py-2">Chart of Account</a>
                    <a href="<?php echo e(route('admin.accounts.sub-accounts')); ?>" class="side-link py-2 <?php echo e(request()->routeIs('admin.accounts.sub-accounts') ? 'active' : ''); ?>">Sub Account List</a>
                    <a href="<?php echo e(route('admin.accounts.predefined-accounts')); ?>" class="side-link py-2 <?php echo e(request()->routeIs('admin.accounts.predefined-accounts') ? 'active' : ''); ?>">Predefined Accounts</a>
                    <a href="<?php echo e(route('admin.accounts.financial-years')); ?>" class="side-link py-2 <?php echo e(request()->routeIs('admin.accounts.financial-years') ? 'active' : ''); ?>">Financial Year</a>
                    <a href="<?php echo e(route('admin.accounts.opening-balances')); ?>" class="side-link py-2 <?php echo e(request()->routeIs('admin.accounts.opening-balances') ? 'active' : ''); ?>">Opening Balance</a>
                    <a href="<?php echo e(route('admin.accounts.payment-methods')); ?>" class="side-link py-2 <?php echo e(request()->routeIs('admin.accounts.payment-methods') ? 'active' : ''); ?>">Payment Methods</a>
                    <a href="<?php echo e(route('admin.accounts.settlement','supplier-payment')); ?>" class="side-link py-2">Supplier Payment</a>
                    <a href="<?php echo e(route('admin.accounts.settlement','customer-receive')); ?>" class="side-link py-2">Customer Receive</a>
                    <a href="<?php echo e(route('admin.accounts.cash-adjustment')); ?>" class="side-link py-2">Cash Adjustment</a>
                    <a href="<?php echo e(route('admin.accounts.vouchers', ['type' => 'debit'])); ?>" class="side-link py-2 <?php echo e(request('type') === 'debit' ? 'active' : ''); ?>">Debit Voucher</a>
                    <a href="<?php echo e(route('admin.accounts.vouchers', ['type' => 'credit'])); ?>" class="side-link py-2 <?php echo e(request('type') === 'credit' ? 'active' : ''); ?>">Credit Voucher</a>
                    <a href="<?php echo e(route('admin.accounts.vouchers', ['type' => 'contra'])); ?>" class="side-link py-2 <?php echo e(request('type') === 'contra' ? 'active' : ''); ?>">Contra Voucher</a>
                    <a href="<?php echo e(route('admin.accounts.vouchers', ['type' => 'journal'])); ?>" class="side-link py-2 <?php echo e(request('type') === 'journal' ? 'active' : ''); ?>">Journal Voucher</a>
                    <?php ($reportsOpen = request()->routeIs('admin.accounts.reports') || request()->routeIs('admin.reports.*')); ?>
                    <button type="button" class="side-link border-0 w-100 text-start py-2 <?php echo e($reportsOpen ? 'active' : ''); ?>" data-admin-collapse="#accounts-reports-menu" aria-expanded="<?php echo e($reportsOpen ? 'true' : 'false'); ?>"><i class="bi bi-bar-chart-line"></i> Reports <i class="bi bi-chevron-down"></i></button>
                    <div id="accounts-reports-menu" class="collapse <?php echo e($reportsOpen ? 'show' : ''); ?>"><div class="ms-3 border-start">
                        <div class="px-3 pt-2 pb-1 small text-uppercase text-muted fw-semibold">Financial reports</div>
                        <?php $__currentLoopData = \App\Http\Controllers\Admin\AccountReportController::FINANCIAL_REPORTS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('admin.accounts.reports',$key)); ?>" class="side-link py-2 <?php echo e(request()->route('report')===$key?'active':''); ?>"><?php echo e($label); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-3 pt-3 pb-1 small text-uppercase text-muted fw-semibold">Business reports</div>
                        <?php $__currentLoopData = \App\Http\Controllers\Admin\AccountReportController::OPERATIONAL_REPORTS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('admin.accounts.reports',$key)); ?>" class="side-link py-2 <?php echo e(request()->route('report')===$key?'active':''); ?>"><?php echo e($label); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div></div>
                </div></div>
                <a href="<?php echo e(route('admin.media.index')); ?>" class="side-link <?php echo e(request()->routeIs('admin.media.*') ? 'active' : ''); ?>"><i class="bi bi-images"></i> Media library</a>

                <p class="nav-label">Administration</p>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="side-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>"><i class="bi bi-people-fill"></i> Users</a>
                <a href="<?php echo e(route('admin.roles.index')); ?>" class="side-link <?php echo e(request()->routeIs('admin.roles.*') ? 'active' : ''); ?>"><i class="bi bi-shield-lock-fill"></i> Roles & Permissions</a>
            <?php endif; ?>
        </nav>
    </aside>
    <button class="sidebar-backdrop" type="button" aria-label="Close navigation" data-sidebar-close></button>

    <main class="flex-grow-1 min-vw-0">
        <header class="topbar px-3 px-lg-4 d-flex align-items-center">
            <button class="icon-button me-3" type="button" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false"><i class="bi bi-list fs-4"></i></button>
            <div><div class="topbar-title">Ecommerce & ERP</div><div class="topbar-subtitle"><?php echo e($superAdmin ? 'Super Admin · all warehouses' : auth()->user()->warehouse?->name.' · '.str_replace('_',' ',auth()->user()->role)); ?></div></div>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="ms-auto"><?php echo csrf_field(); ?><button class="btn btn-sm btn-outline-secondary">Sign out</button></form>
        </header>
        <div class="page-content">
            <?php if(session('success')): ?><div class="alert alert-success border-0 rounded-3 mb-4"><?php echo e(session('success')); ?></div><?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>
</div>
<div class="modal fade" id="media-picker-modal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Choose from Media Library</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div id="media-picker-content" class="modal-body"></div></div></div></div>
</body>
</html>
<?php /**PATH F:\laragon\www\Ecommerce\resources\views\layouts\admin.blade.php ENDPATH**/ ?>
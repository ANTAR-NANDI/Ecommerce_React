<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Shop the newest products and everyday deals.">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>EBay · Shop with confidence</title>
    <?php echo app('Illuminate\Foundation\Vite')->reactRefresh(); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/storefront.css', 'resources/js/storefront.jsx']); ?>
</head>
<body>
    <div id="storefront-root"></div>
</body>
</html>
<?php /**PATH F:\laragon\www\Ecommerce\resources\views\storefront.blade.php ENDPATH**/ ?>
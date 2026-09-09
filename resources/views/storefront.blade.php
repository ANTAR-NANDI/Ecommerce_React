<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Shop the newest products and everyday deals.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VeloraCommerce · Shop with confidence</title>
    @viteReactRefresh
    @vite(['resources/css/storefront.css', 'resources/js/storefront.jsx'])
</head>
<body>
    <div id="storefront-root"></div>
</body>
</html>

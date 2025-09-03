<?php
require_once __DIR__.'/db.php';
require_once __DIR__.'/functions.php';
// Uncomment the line below to (re)import products manually (very first time).
// importProductsFromFolders($conn, realpath(__DIR__.'/../assets/images'));

// Or trigger via URL parameter once: http://localhost:81/mini-ecommerce/?import=1
if(isset($_GET['import']) && $_GET['import']==='1'){
    $inserted = importProductsFromFolders($conn, realpath(__DIR__.'/../assets/images'));
    echo "<p style='position:fixed;top:0;left:0;right:0;z-index:9999;background:#ffc107;color:#000;padding:6px;text-align:center'>Imported $inserted products from images – remove '?import=1' from the URL once finished.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniShop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/mini-ecommerce/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="/mini-ecommerce/assets/images/favicon.ico">
    <style>
        .announcement-bar {background:linear-gradient(90deg,#6610f2 0%,#0d6efd 100%);color:#fff;font-size:.98rem;padding:.5rem 0;letter-spacing:.01em;}
        .announcement-bar .bi {margin-right:.5em;}
        .header-contact {font-size:.95rem; color:#fff; opacity:.85;}
        .header-contact .bi {margin-right:.3em;}
        .payment-icons img {height:28px;margin-right:8px;filter:grayscale(1) brightness(1.2);opacity:.8;}
        @media (max-width: 768px) {.header-contact,.payment-icons{display:none;}}
        .sticky-top-custom {position:sticky;top:0;z-index:1040;}
        .navbar-search {max-width:320px;}
    </style>
</head>
<body>
<!-- Announcement Bar -->
<div class="announcement-bar text-center navbar-gradient">
    <span><i class="bi bi-truck"></i> Free shipping on orders over $50 &nbsp; | &nbsp; <i class="bi bi-shield-check"></i> 100% Secure Checkout &nbsp; | &nbsp; <i class="bi bi-clock-history"></i> 24/7 Customer Support</span>
</div>
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top-custom shadow-sm">
    <div class="container py-1">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="/mini-ecommerce/index.php"><i class="bi bi-bag-heart-fill text-primary me-2"></i><span>MiniShop</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/mini-ecommerce/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/mini-ecommerce/products.php">Products</a></li>
            </ul>
            <form class="d-flex navbar-search me-lg-3 mb-2 mb-lg-0" role="search" action="/mini-ecommerce/products.php" method="get">
                <input class="form-control form-control-sm" type="search" name="q" placeholder="Search products..." aria-label="Search">
                <button class="btn btn-outline-light btn-sm ms-2" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/mini-ecommerce/cart.php">Cart (<?php echo cartCount(); ?>)</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="/mini-ecommerce/wishlist.php">Wishlist</a></li>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/mini-ecommerce/logout.php">Logout</a></li>
                            <?php if($_SESSION['user_id'] === 1 && file_exists(__DIR__ . '/../admin/dashboard.php')): ?>
                                <li><a class="dropdown-item" href="/mini-ecommerce/admin/dashboard.php">Admin Panel</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/mini-ecommerce/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/mini-ecommerce/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="pt-5"> 
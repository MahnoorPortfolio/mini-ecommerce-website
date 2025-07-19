<?php
require_once __DIR__.'/includes/header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?msg=login_wishlist');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['action'])) {
    $product_id = (int)$_POST['product_id'];
    $action = $_POST['action'];
    if ($action === 'add') {
        addToWishlist($conn, $product_id);
    } elseif ($action === 'remove') {
        removeFromWishlist($conn, $product_id);
    }
}
$redirect = $_SERVER['HTTP_REFERER'] ?? 'products.php';
header('Location: ' . $redirect);
exit; 
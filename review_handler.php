<?php
require_once __DIR__.'/includes/header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?msg=login_review');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $action = $_POST['action'] ?? '';
    if ($action === 'delete' && isset($_POST['review_id'])) {
        deleteReview($conn, (int)$_POST['review_id']);
    } elseif ($action === 'edit' && isset($_POST['review_id'], $_POST['rating'], $_POST['review'])) {
        $rating = max(1, min(5, (int)$_POST['rating']));
        $review = trim($_POST['review'] ?? '');
        updateReview($conn, (int)$_POST['review_id'], $rating, $review);
    } elseif (isset($_POST['product_id'], $_POST['rating'])) {
        $rating = max(1, min(5, (int)$_POST['rating']));
        $review = trim($_POST['review'] ?? '');
        addOrUpdateReview($conn, $product_id, $rating, $review);
    }
    header('Location: product_detail.php?id=' . $product_id);
    exit;
}
header('Location: products.php');
exit; 
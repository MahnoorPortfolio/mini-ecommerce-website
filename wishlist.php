<?php
require_once __DIR__.'/includes/header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?msg=login_wishlist');
    exit;
}
$products = getWishlistProducts($conn);
?>
<div class="container py-5">
    <h1 class="section-title mb-4">My Wishlist</h1>
    <?php if (!$products): ?>
        <div class="text-center py-5">
            <i class="bi bi-heart display-3 text-muted mb-3"></i>
            <h4 class="mb-3">Your wishlist is empty.</h4>
            <a href="products.php" class="btn btn-gradient"><i class="bi bi-bag-plus"></i> Browse Products</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $prod): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card product-card h-100">
                        <div class="position-relative">
                            <img src="/mini-ecommerce/<?php echo htmlspecialchars($prod['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title mb-2 truncate-1"><?php echo htmlspecialchars($prod['name']); ?></h6>
                            <span class="card-price mb-3 mt-auto"><?php echo formatPrice($prod['price']); ?></span>
                            <form method="post" action="wishlist_handler.php" class="mb-2">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                <button class="btn btn-outline-danger w-100"><i class="bi bi-heart-fill"></i> Remove</button>
                            </form>
                            <a href="product_detail.php?id=<?php echo $prod['id']; ?>" class="btn btn-outline-primary w-100"><i class="bi bi-eye"></i> View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?> 
<?php
require_once __DIR__.'/includes/header.php';

// Pick a random hero banner
$heroBanners = [
    'assets/images/hero-banner.png',
    'assets/images/hero-banner-2.png',
    'assets/images/hero-banner-3.png',
    'assets/images/hero-banner-4.png',
    'assets/images/hero-banner-5.png',
];
$heroBanner = $heroBanners[array_rand($heroBanners)];

// Fetch distinct categories
$catResult = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");

// Fetch featured or latest products (example: latest 8)
$products = $conn->query("SELECT id,name,price,image,description FROM products ORDER BY id DESC LIMIT 8");
?>
<section class="hero text-white">
    <div class="hero-banner-bg" style="background-image:url('/mini-ecommerce/<?php echo $heroBanner; ?>');"></div>
    <h1 data-sr>Welcome to MiniShop</h1>
    <p class="lead" data-sr>Discover great products at unbeatable prices!</p>
    <a href="/mini-ecommerce/products.php" class="btn btn-gradient btn-lg" data-sr>Shop Now</a>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="section-title" data-sr>Featured Products</h2>
        <div class="mb-4">
            <div class="d-flex flex-wrap gap-2">
                <?php
                // Reset pointer and fetch categories again for nav
                $catResult->data_seek(0);
                while($cat = $catResult->fetch_assoc()):
                    $catName = $cat['category'];
                ?>
                    <a href="products.php?category=<?php echo rawurlencode($catName); ?>" class="btn btn-outline-primary btn-sm">
                        <?php echo htmlspecialchars($catName); ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        <?php if($products->num_rows === 0): ?>
            <div class="text-center py-5">
                <i class="bi bi-box-seam display-3 text-muted mb-3"></i>
                <h4 class="mb-3">No products available at the moment.</h4>
                <a href="products.php" class="btn btn-gradient"><i class="bi bi-bag-plus"></i> Browse All Products</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php while($row = $products->fetch_assoc()): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card product-card h-100">
                            <div class="position-relative">
                                <img src="/mini-ecommerce/<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <form method="post" action="wishlist_handler.php" class="wishlist-btn-float">
                                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                        <?php if(isInWishlist($conn, $row['id'])): ?>
                                            <input type="hidden" name="action" value="remove">
                                            <button class="btn btn-link p-0 text-danger" title="Remove from Wishlist"><i class="bi bi-heart-fill"></i></button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="add">
                                            <button class="btn btn-link p-0 text-secondary" title="Add to Wishlist"><i class="bi bi-heart"></i></button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title mb-2 truncate-1"><?php echo htmlspecialchars($row['name']); ?></h6>
                                <div class="product-description mb-2 truncate-2"><?php echo htmlspecialchars($row['description']); ?></div>
                                <span class="card-price mb-3 mt-auto"><?php echo formatPrice($row['price']); ?></span>
                                <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary w-100"><i class="bi bi-eye"></i> View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-5">
                <a href="products.php" class="btn btn-gradient btn-lg"><i class="bi bi-grid"></i> View All Products</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php if (!isset($modalAdded)) { $modalAdded = true; ?>
<!-- Description Modal -->
<div class="modal fade" id="descModal" tabindex="-1" aria-labelledby="descModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="descModalLabel">Product Description</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body fade-in" id="descModalBody"></div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.read-more').forEach(function(el) {
    el.addEventListener('click', function() {
      var desc = this.getAttribute('data-desc');
      var title = this.getAttribute('data-title') || 'Product Description';
      document.getElementById('descModalLabel').textContent = title;
      var body = document.getElementById('descModalBody');
      body.textContent = desc;
      body.classList.remove('fade-in');
      void body.offsetWidth; // trigger reflow
      body.classList.add('fade-in');
      var modal = new bootstrap.Modal(document.getElementById('descModal'));
      modal.show();
    });
  });
});
</script>
<style>
.fade-in { animation: fadeInDesc .5s; }
@keyframes fadeInDesc { from { opacity: 0; } to { opacity: 1; } }
</style>
<?php } ?>
<?php require_once __DIR__.'/includes/footer.php'; ?> 
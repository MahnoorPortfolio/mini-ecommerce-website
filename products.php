<?php
require_once __DIR__.'/includes/header.php';
// Fetch all categories for nav
$catNav = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$category = $_GET['category'] ?? '';
$q = trim($_GET['q'] ?? '');
$params = [];
$where = [];
$sql = "SELECT id,name,price,image,description FROM products";
if($category) {
    $where[] = "category=?";
    $params[] = $category;
}
if($q !== '') {
    $where[] = "name LIKE ?";
    $params[] = "%$q%";
}
if($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY id DESC";
$stmt = $conn->prepare($sql);
if($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();
?>
<div class="container py-5">
    <h1 class="section-title" data-sr>All Products</h1>
    <div class="mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="products.php<?php echo $q ? '?q=' . urlencode($q) : ''; ?>" class="btn btn-outline-primary btn-sm<?php if(!$category) echo ' active'; ?>">All</a>
            <?php while($cat = $catNav->fetch_assoc()):
                $catName = $cat['category'];
                $active = ($category === $catName) ? ' active' : '';
                $link = 'products.php?category=' . rawurlencode($catName);
                if($q) $link .= '&q=' . urlencode($q);
            ?>
                <a href="<?php echo $link; ?>" class="btn btn-outline-primary btn-sm<?php echo $active; ?>">
                    <?php echo htmlspecialchars($catName); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
    <form class="mb-4" method="get" action="products.php">
        <?php if($category): ?><input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>"><?php endif; ?>
        <div class="input-group" style="max-width:400px;">
            <input type="text" class="form-control" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars($q); ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <?php if($q): ?>
        <p class="mb-3">Search results for <strong><?php echo htmlspecialchars($q); ?></strong>:</p>
    <?php endif; ?>
    <?php if($products->num_rows===0): ?>
        <p>No products found.</p>
    <?php else: ?>
        <div class="row g-4">
            <?php while($row=$products->fetch_assoc()): ?>
                <div class="col-sm-6 col-md-4 col-lg-3" data-sr>
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
                            <div class="product-description mb-2 truncate-2">
                                <?php
                                $desc = htmlspecialchars($row['description']);
                                $trunc = 70;
                                $prodName = htmlspecialchars($row['name']);
                                if(strlen($desc) > $trunc) {
                                    $short = substr($desc, 0, $trunc) . '… <span class=\'read-more\' data-desc="' . htmlspecialchars($row['description'], ENT_QUOTES) . '" data-title="' . $prodName . '">Read more</span>';
                                    echo $short;
                                } else {
                                    echo $desc;
                                }
                                ?>
                            </div>
                            <span class="card-price mb-3 mt-auto"><?php echo formatPrice($row['price']); ?></span>
                            <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
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
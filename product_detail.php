<?php
require_once __DIR__.'/includes/header.php';

// Validate id param
$id = isset($_GET['id'])? (int)$_GET['id'] : 0;
if($id<=0){
    echo '<div class="container py-5"><p>Invalid product ID.</p></div>';
    require_once __DIR__.'/includes/footer.php';
    exit;
}
$stmt = $conn->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
$stmt->bind_param('i',$id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if(!$product){
    echo '<div class="container py-5"><p>Product not found.</p></div>';
    require_once __DIR__.'/includes/footer.php';
    exit;
}

// Handle add to cart
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_to_cart'])){
    $qty = max(1,(int)($_POST['quantity']??1));
    addToCart($product['id'],$qty);
    header('Location: cart.php');
    exit;
}

// Rating stats and reviews
$stats = getProductRatingStats($conn, $product['id']);
$reviews = getProductReviews($conn, $product['id']);
$userReview = getUserReviewForProduct($conn, $product['id']);
?>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-6">
            <img src="/mini-ecommerce/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid rounded shadow-sm">
        </div>
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <?php if(isset($_SESSION['user_id'])): ?>
                <form method="post" action="wishlist_handler.php" class="mb-2" style="display:inline">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <?php if(isInWishlist($conn, $product['id'])): ?>
                        <input type="hidden" name="action" value="remove">
                        <button class="btn btn-link p-0 text-danger" title="Remove from Wishlist"><i class="bi bi-heart-fill"></i> Remove from Wishlist</button>
                    <?php else: ?>
                        <input type="hidden" name="action" value="add">
                        <button class="btn btn-link p-0 text-secondary" title="Add to Wishlist"><i class="bi bi-heart"></i> Add to Wishlist</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
            <h4 class="text-primary mb-3"><?php echo formatPrice($product['price']); ?></h4>
            <div class="mb-2 product-detail-stars">
                <?php
                $avg = $stats['avg_rating'] ? round($stats['avg_rating'],1) : 0;
                $count = (int)$stats['count'];
                for($i=1;$i<=5;$i++){
                    echo '<i class="bi '.($i<=$avg?'bi-star-fill text-warning':'bi-star text-secondary').'"></i>';
                }
                echo $count ? " <span class='ms-2'>($avg/5 from $count review".($count>1?'s':'').")</span>" : " <span class='ms-2 text-muted'>(No reviews yet)</span>";
                ?>
            </div>
            <hr class="review-divider">
            <div class="product-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
            <p class="text-muted mb-4">Category: <?php echo htmlspecialchars($product['category']); ?></p>
            <form method="post" class="d-flex gap-2 mb-3">
                <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width:120px;">
                <button name="add_to_cart" class="btn btn-gradient">Add to Cart</button>
            </form>
            <a href="products.php" class="btn btn-link">&larr; Back to products</a>
        </div>
    </div>
</div>
<div class="container pb-5">
    <h3 class="mt-5 mb-3">Customer Reviews</h3>
    <?php if($reviews): ?>
        <?php foreach($reviews as $r): ?>
            <div class="card review-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="review-author"><?php echo htmlspecialchars($r['username']); ?></span>
                        <span class="review-rating ms-3">
                            <?php for($i=1;$i<=5;$i++) echo '<i class=\"bi '.($i<=$r['rating']?'bi-star-fill text-warning':'bi-star text-secondary').'\"></i>'; ?>
                        </span>
                        <span class="review-date ms-3"><?php echo date('Y-m-d', strtotime($r['created_at'])); ?></span>
                    </div>
                    <div><?php echo nl2br(htmlspecialchars($r['review'])); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No reviews yet. Be the first to review this product!</div>
    <?php endif; ?>
    <?php if(isset($_SESSION['user_id'])): ?>
        <?php if(!$userReview): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Write a Review</h5>
                    <form method="post" action="review_handler.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="mb-2">
                            <label class="form-label">Rating:</label>
                            <select name="rating" class="form-select w-auto d-inline-block" required>
                                <option value="">Select</option>
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> Star<?php if($i>1)echo 's'; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Review:</label>
                            <textarea name="review" class="form-control" rows="3" placeholder="Share your experience..." required></textarea>
                        </div>
                        <button class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Your Review</h5>
                    <div class="mb-2">
                        <span class="review-rating">
                            <?php for($i=1;$i<=5;$i++) echo '<i class=\"bi '.($i<=$userReview['rating']?'bi-star-fill text-warning':'bi-star text-secondary').'\"></i>'; ?>
                        </span>
                        <span class="review-date ms-3"><?php echo date('Y-m-d', strtotime($userReview['created_at'])); ?></span>
                    </div>
                    <div class="mb-2"><?php echo nl2br(htmlspecialchars($userReview['review'])); ?></div>
                    <form method="post" action="review_handler.php" class="d-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="review_id" value="<?php echo $userReview['id']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                    <button class="btn btn-outline-primary btn-sm ms-2" onclick="document.getElementById('editReviewForm').style.display='block';this.style.display='none';return false;"><i class="bi bi-pencil"></i> Edit</button>
                    <form method="post" action="review_handler.php" id="editReviewForm" style="display:none; margin-top:1em;">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="review_id" value="<?php echo $userReview['id']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="mb-2">
                            <label class="form-label">Rating:</label>
                            <select name="rating" class="form-select w-auto d-inline-block" required>
                                <option value="">Select</option>
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <option value="<?php echo $i; ?>"<?php if($userReview['rating']==$i) echo ' selected'; ?>><?php echo $i; ?> Star<?php if($i>1)echo 's'; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Review:</label>
                            <textarea name="review" class="form-control" rows="3" required><?php echo htmlspecialchars($userReview['review']); ?></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm">Save Changes</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-warning mt-4">Please <a href="login.php">login</a> to write a review.</div>
    <?php endif; ?>
</div>
<?php
// Related products (same category, not this product)
$stmt = $conn->prepare('SELECT id,name,price,image FROM products WHERE category=? AND id!=? ORDER BY id DESC LIMIT 4');
$stmt->bind_param('si',$product['category'],$product['id']);
$stmt->execute();
$related = $stmt->get_result();
if($related->num_rows): ?>
<div class="container pb-5">
    <h3 class="section-title mt-5" data-sr>Related Products</h3>
    <div class="row g-4">
        <?php while($row = $related->fetch_assoc()): ?>
            <div class="col-sm-6 col-md-4 col-lg-3" data-sr>
                <div class="card product-card h-100">
                    <img src="/mini-ecommerce/<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-2"><?php echo htmlspecialchars($row['name']); ?></h6>
                        <span class="card-price mb-3 mt-auto"><?php echo formatPrice($row['price']); ?></span>
                        <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__.'/includes/footer.php'; ?> 
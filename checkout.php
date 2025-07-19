<?php
require_once __DIR__.'/includes/header.php';
if(!isset($_SESSION['user_id'])){
    header('Location: login.php?msg=login_checkout');
    exit;
}
$items = $_SESSION['cart'] ?? [];
if(!$items){
    echo '<div class="container py-5"><h2>Your cart is empty.</h2><a href="products.php" class="btn btn-gradient mt-3">Shop Now</a></div>';
    require_once __DIR__.'/includes/footer.php';
    exit;
}
// Fetch product details
$productDetails = [];
$ids = array_column($items,'product_id');
$placeholders = implode(',',array_fill(0,count($ids),'?'));
$types = str_repeat('i',count($ids));
$stmt = $conn->prepare("SELECT id,name,price,image FROM products WHERE id IN ($placeholders)");
$stmt->bind_param($types,...$ids);
$stmt->execute();
$res = $stmt->get_result();
while($row=$res->fetch_assoc()){$productDetails[$row['id']]=$row;}
$total = 0;
foreach($items as $item){
    $prod = $productDetails[$item['product_id']] ?? null;
    if($prod) $total += $prod['price'] * $item['quantity'];
}
$shipping = 199;
$grandTotal = $total + $shipping;
?>
<div class="container py-5" style="max-width:900px;">
    <h1 class="mb-4 section-title">Checkout</h1>
    <form method="post" action="order_confirm.php" class="row g-4">
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Contact</h5>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email address" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="phone" class="form-control" placeholder="Phone (optional)">
                    </div>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Delivery</h5>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <input type="text" name="first_name" class="form-control" placeholder="First name" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="last_name" class="form-control" placeholder="Last name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="address" class="form-control" placeholder="Address" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="city" class="form-control" placeholder="City" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <input type="text" name="postal_code" class="form-control" placeholder="Postal code (optional)">
                        </div>
                        <div class="col-md-6">
                            <select name="country" class="form-select" required>
                                <option value="Pakistan">Pakistan</option>
                                <option value="USA">USA</option>
                                <option value="UK">UK</option>
                                <option value="India">India</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Payment</h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="COD" checked>
                        <label class="form-check-label" for="cod">Cash on Delivery (COD)</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="card" value="Card">
                        <label class="form-check-label" for="card">Pay by Card (Visa/Mastercard/UnionPay)</label>
                    </div>
                    <div id="cardFields" style="display:none;">
                        <div class="mb-2">
                            <input type="text" class="form-control" name="card_number" placeholder="Card Number">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col">
                                <input type="text" class="form-control" name="card_expiry" placeholder="MM/YY">
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" name="card_cvv" placeholder="CVV">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Billing Address</h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="billing_same" id="billing_same" value="1" checked>
                        <label class="form-check-label" for="billing_same">Same as shipping address</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="billing_same" id="billing_diff" value="0">
                        <label class="form-check-label" for="billing_diff">Use a different billing address</label>
                    </div>
                    <div id="billingFields" style="display:none;">
                        <div class="mb-2">
                            <input type="text" class="form-control" name="billing_address" placeholder="Billing Address">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="billing_city" placeholder="Billing City">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="billing_postal" placeholder="Billing Postal Code">
                        </div>
                        <div class="mb-2">
                            <select name="billing_country" class="form-select">
                                <option value="Pakistan">Pakistan</option>
                                <option value="USA">USA</option>
                                <option value="UK">UK</option>
                                <option value="India">India</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn btn-gradient w-100 py-2 fs-5"><i class="bi bi-cart-check"></i> Complete Order</button>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Order Summary</h5>
                    <ul class="list-group mb-3">
                        <?php foreach($items as $item):
                            $prod = $productDetails[$item['product_id']] ?? null; if(!$prod) continue;
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <img src="/mini-ecommerce/<?php echo htmlspecialchars($prod['image']); ?>" width="48" height="48" style="object-fit:cover;border-radius:6px;">
                                <span><?php echo htmlspecialchars($prod['name']); ?> x<?php echo $item['quantity']; ?></span>
                            </div>
                            <span><?php echo formatPrice($prod['price'] * $item['quantity']); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span><?php echo formatPrice($total); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span><?php echo formatPrice($shipping); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 fw-bold">
                        <span>Total</span>
                        <span><?php echo formatPrice($grandTotal); ?></span>
                    </div>
                </div>
            </div>
            <div class="text-center small text-muted">
                By placing your order, you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.
            </div>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name=payment_method]').forEach(function(el) {
        el.addEventListener('change', function() {
            document.getElementById('cardFields').style.display = (this.value === 'Card') ? '' : 'none';
        });
    });
    document.querySelectorAll('input[name=billing_same]').forEach(function(el) {
        el.addEventListener('change', function() {
            document.getElementById('billingFields').style.display = (this.value === '0') ? '' : 'none';
        });
    });
});
</script>
<?php require_once __DIR__.'/includes/footer.php'; ?> 
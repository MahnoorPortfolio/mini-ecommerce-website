<?php
require_once __DIR__.'/includes/header.php';

// Handle cart actions
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])){
    $action = $_POST['action'];
    $id     = isset($_POST['id'])? (int)$_POST['id'] : 0;
    $qty    = isset($_POST['quantity'])? (int)$_POST['quantity'] : 1;

    switch($action){
        case 'update':
            updateCartQuantity($id,$qty);
            break;
        case 'remove':
            removeFromCart($id);
            break;
        case 'clear':
            unset($_SESSION['cart']);
            break;
    }
    header('Location: cart.php');
    exit;
}

$items = $_SESSION['cart'] ?? [];
$productDetails = [];
if($items){
    $ids = array_column($items,'product_id');
    $placeholders = implode(',',array_fill(0,count($ids),'?'));
    $types = str_repeat('i',count($ids));
    $stmt = $conn->prepare("SELECT id,name,price,image FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types,...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row=$res->fetch_assoc()){$productDetails[$row['id']]=$row;}
}
?>
<div class="container py-5">
    <h1 class="section-title" data-sr>Your Cart</h1>
    <?php if(!$items): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x display-3 text-muted mb-3"></i>
            <h4 class="mb-3">Your cart is currently empty.</h4>
            <a href="products.php" class="btn btn-gradient"><i class="bi bi-bag-plus"></i> Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="table-responsive mb-4">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Price</th>
                        <th scope="col" style="width:140px;">Quantity</th>
                        <th scope="col">Subtotal</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item):
                        $prod = $productDetails[$item['product_id']] ?? null; if(!$prod) continue;
                        $subTotal = $prod['price']*$item['quantity'];
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="/mini-ecommerce/<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" width="60" height="60" style="object-fit:cover; border-radius:8px;">
                                <span class="fw-semibold"><?php echo htmlspecialchars($prod['name']); ?></span>
                            </div>
                        </td>
                        <td><?php echo formatPrice($prod['price']); ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control form-control-sm">
                                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-repeat"></i></button>
                            </form>
                        </td>
                        <td><?php echo formatPrice($subTotal); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <h4>Total: <?php echo formatPrice(cartTotal($conn)); ?></h4>
            <div class="d-flex gap-2">
                <form method="post">
                    <input type="hidden" name="action" value="clear">
                    <button class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Clear Cart</button>
                </form>
                <a href="#" class="btn btn-gradient" id="checkoutBtn"><i class="bi bi-credit-card"></i> Checkout</a>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var checkoutBtn = document.getElementById('checkoutBtn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      <?php if(!isset($_SESSION['user_id'])): ?>
        window.location.href = 'login.php?msg=login_checkout';
      <?php else: ?>
        window.location.href = 'checkout.php';
      <?php endif; ?>
    });
  }
});
</script>
<?php require_once __DIR__.'/includes/footer.php'; ?> 
<?php
require_once __DIR__.'/includes/header.php';
if(!isset($_SESSION['user_id'])){
    header('Location: login.php?msg=login_checkout');
    exit;
}
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: checkout.php');
    exit;
}
// Get form data
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$postal_code = $_POST['postal_code'] ?? '';
$country = $_POST['country'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$billing_same = $_POST['billing_same'] ?? '1';
$billing_address = $_POST['billing_address'] ?? $address;
$billing_city = $_POST['billing_city'] ?? $city;
$billing_postal = $_POST['billing_postal'] ?? $postal_code;
$billing_country = $_POST['billing_country'] ?? $country;
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
// Simulate order ID
$order_id = rand(100000,999999);
// Clear cart
unset($_SESSION['cart']);
// Store order details in session for PDF invoice
$_SESSION['last_order_items'] = [];
foreach($items as $item){
    $prod = $productDetails[$item['product_id']] ?? null;
    if($prod){
        $_SESSION['last_order_items'][] = [
            'name' => $prod['name'],
            'quantity' => $item['quantity'],
            'price' => $prod['price']
        ];
    }
}
$_SESSION['last_order_first_name'] = $first_name;
$_SESSION['last_order_last_name'] = $last_name;
$_SESSION['last_order_email'] = $email;
$_SESSION['last_order_phone'] = $phone;
$_SESSION['last_order_address'] = $address;
$_SESSION['last_order_city'] = $city;
$_SESSION['last_order_postal_code'] = $postal_code;
$_SESSION['last_order_country'] = $country;
$_SESSION['last_order_payment_method'] = $payment_method;
$_SESSION['last_order_total'] = $total;
$_SESSION['last_order_shipping'] = $shipping;
$_SESSION['last_order_grandTotal'] = $grandTotal;
// Send confirmation email using PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__.'/includes/phpmailer/PHPMailer.php';
require_once __DIR__.'/includes/phpmailer/SMTP.php';
require_once __DIR__.'/includes/phpmailer/Exception.php';
$mailSent = false;
$mailError = '';
try {
    $mail = new PHPMailer(true);
    // SMTP config - FILL IN YOUR GMAIL AND APP PASSWORD BELOW
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mm0453Prince@gmail.com'; // <-- your Gmail address
    $mail->Password = 'bftd zgpt bkzs ahcz';    // <-- your Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('mm0453Prince@gmail.com', 'MiniShop');
    $mail->addAddress($email, $first_name . ' ' . $last_name);
    $mail->isHTML(true);
    $mail->Subject = "Your MiniShop Order #$order_id Confirmation";
    // Build HTML message
    $message = '
    <div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;border:1px solid #eee;border-radius:10px;overflow:hidden;">
      <div style="background:linear-gradient(90deg,#6610f2 0%,#0d6efd 100%);color:#fff;padding:18px 24px;">
        <h2 style="margin:0;font-weight:700;letter-spacing:1px;">MiniShop</h2>
        <p style="margin:0;font-size:1.1em;">Thank you for your order!</p>
      </div>
      <div style="padding:24px;">
        <h3 style="margin-top:0;">Order #'.$order_id.'</h3>
        <p>
          <strong>Name:</strong> '.htmlspecialchars($first_name.' '.$last_name).'<br>
          <strong>Email:</strong> '.htmlspecialchars($email).'<br>
          <strong>Shipping Address:</strong> '.htmlspecialchars($address).', '.htmlspecialchars($city).', '.htmlspecialchars($country).' '.htmlspecialchars($postal_code).'<br>
          <strong>Payment Method:</strong> '.htmlspecialchars($payment_method).'
        </p>
        <h4 style="margin-bottom:8px;">Order Summary</h4>
        <table style="width:100%;border-collapse:collapse;">
          <thead>
            <tr style="background:#f6f6f6;">
              <th align="left" style="padding:8px 6px;">Product</th>
              <th align="center" style="padding:8px 6px;">Qty</th>
              <th align="right" style="padding:8px 6px;">Price</th>
            </tr>
          </thead>
          <tbody>';
    foreach($items as $item){
        $prod = $productDetails[$item['product_id']] ?? null; if(!$prod) continue;
        $message .= '<tr>
          <td style="padding:6px 6px;border-bottom:1px solid #eee;">'.htmlspecialchars($prod['name']).'</td>
          <td align="center" style="padding:6px 6px;border-bottom:1px solid #eee;">'.$item['quantity'].'</td>
          <td align="right" style="padding:6px 6px;border-bottom:1px solid #eee;">'.formatPrice($prod['price'] * $item['quantity']).'</td>
        </tr>';
    }
    $message .= '
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2" align="right" style="padding:8px 6px;"><strong>Subtotal:</strong></td>
              <td align="right" style="padding:8px 6px;">'.formatPrice($total).'</td>
            </tr>
            <tr>
              <td colspan="2" align="right" style="padding:8px 6px;"><strong>Shipping:</strong></td>
              <td align="right" style="padding:8px 6px;">'.formatPrice($shipping).'</td>
            </tr>
            <tr>
              <td colspan="2" align="right" style="padding:8px 6px;font-size:1.1em;"><strong>Total:</strong></td>
              <td align="right" style="padding:8px 6px;font-size:1.1em;"><strong>'.formatPrice($grandTotal).'</strong></td>
            </tr>
          </tfoot>
        </table>
        <p style="margin-top:24px;">If you have any questions, just reply to this email.<br>
        <span style="color:#6610f2;font-weight:600;">MiniShop Team</span></p>
      </div>
    </div>
    ';
    // Plain text alternative
    $altMessage = "Hello $first_name $last_name,\n\nThank you for your order! Here is your order summary:\n\n";
    foreach($items as $item){
        $prod = $productDetails[$item['product_id']] ?? null; if(!$prod) continue;
        $altMessage .= $prod['name'] . " x" . $item['quantity'] . ": " . formatPrice($prod['price'] * $item['quantity']) . "\n";
    }
    $altMessage .= "\nSubtotal: " . formatPrice($total) . "\n";
    $altMessage .= "Shipping: " . formatPrice($shipping) . "\n";
    $altMessage .= "Total: " . formatPrice($grandTotal) . "\n\n";
    $altMessage .= "Shipping to: $address, $city, $country $postal_code\n";
    $altMessage .= "\nIf you have questions, reply to this email.\n\nMiniShop Team";
    $mail->Body = $message;
    $mail->AltBody = $altMessage;
    $mail->send();
    $mailSent = true;
} catch (Exception $e) {
    $mailSent = false;
    $mailError = $mail->ErrorInfo;
}
?>
<div class="container py-5" style="max-width:800px;">
    <div class="alert alert-success mb-4 d-flex align-items-center"><i class="bi bi-check-circle-fill me-2 fs-4"></i><h3 class="mb-0">Thank you for your order!</h3></div>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3"><i class="bi bi-receipt-cutoff me-2"></i>Order #<?php echo $order_id; ?></h5>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?><br>
            <strong>Email:</strong> <?php echo htmlspecialchars($email); ?><br>
            <strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?><br>
            <strong>Shipping Address:</strong> <?php echo htmlspecialchars($address); ?>, <?php echo htmlspecialchars($city); ?>, <?php echo htmlspecialchars($country); ?> <?php echo htmlspecialchars($postal_code); ?><br>
            <strong>Payment Method:</strong> <?php echo htmlspecialchars($payment_method); ?></p>
            <h6 class="mt-4">Order Summary</h6>
            <ul class="list-group mb-3">
                <?php foreach($items as $item):
                    $prod = $productDetails[$item['product_id']] ?? null; if(!$prod) continue;
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <img src="/mini-ecommerce/<?php echo htmlspecialchars($prod['image']); ?>" width="40" height="40" style="object-fit:cover;border-radius:6px;">
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
    <div class="text-center mb-3">
        <a href="generate_invoice.php?order_id=<?php echo $order_id; ?>" class="btn btn-success" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> Download Invoice (PDF)
        </a>
    </div>
    <div class="text-center">
        <a href="products.php" class="btn btn-gradient"><i class="bi bi-bag-plus"></i> Continue Shopping</a>
    </div>
</div>
<?php if(!$mailSent): ?>
    <div class="alert alert-warning d-flex align-items-center mt-4"><i class="bi bi-exclamation-triangle-fill me-2"></i>Order placed, but confirmation email could not be sent.</div>
    <?php if(!empty($mailError)): ?>
        <div class="alert alert-danger d-flex align-items-center mt-2"><i class="bi bi-x-octagon-fill me-2"></i>Mailer Error: <?php echo htmlspecialchars($mailError); ?></div>
    <?php endif; ?>
<?php endif; ?>
<?php require_once __DIR__.'/includes/footer.php'; ?> 
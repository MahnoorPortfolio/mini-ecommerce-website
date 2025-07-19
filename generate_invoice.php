<?php
require_once __DIR__.'/fpdf/fpdf.php';

// For demo: get order details from GET params (in production, fetch from DB or session)
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if(!$order_id) die('Order ID missing.');

// Simulate fetching order details (in real app, fetch from DB)
// For now, use session or fallback demo data
session_start();
$items = $_SESSION['last_order_items'] ?? [];
$first_name = $_SESSION['last_order_first_name'] ?? 'Customer';
$last_name = $_SESSION['last_order_last_name'] ?? '';
$email = $_SESSION['last_order_email'] ?? '';
$phone = $_SESSION['last_order_phone'] ?? '';
$address = $_SESSION['last_order_address'] ?? '';
$city = $_SESSION['last_order_city'] ?? '';
$postal_code = $_SESSION['last_order_postal_code'] ?? '';
$country = $_SESSION['last_order_country'] ?? '';
$payment_method = $_SESSION['last_order_payment_method'] ?? '';
$total = $_SESSION['last_order_total'] ?? 0;
$shipping = $_SESSION['last_order_shipping'] ?? 199;
$grandTotal = $_SESSION['last_order_grandTotal'] ?? ($total+$shipping);

$pdf = new FPDF();
$pdf->AddPage();
// Logo and Title
$pdf->SetFont('Arial','B',20);
$pdf->SetTextColor(102,16,242); // Brand purple
$pdf->Cell(0,15,'MiniShop',0,1,'C');
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Invoice',0,1,'C');
$pdf->Ln(2);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,'Order ID: '.$order_id,0,1);
$pdf->Cell(0,10,'Date: '.date('Y-m-d'),0,1);
$pdf->Cell(0,10,'Customer: '.$first_name.' '.$last_name,0,1);
$pdf->Cell(0,10,'Email: '.$email,0,1);
$pdf->Cell(0,10,'Phone: '.$phone,0,1);
$pdf->Cell(0,10,'Shipping Address: '.$address.', '.$city.', '.$country.' '.$postal_code,0,1);
$pdf->Cell(0,10,'Payment Method: '.$payment_method,0,1);
$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(102,16,242);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(80,10,'Product',1,0,'C',true);
$pdf->Cell(30,10,'Qty',1,0,'C',true);
$pdf->Cell(40,10,'Price',1,0,'C',true);
$pdf->Cell(40,10,'Total',1,1,'C',true);
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0,0,0);
foreach($items as $item){
    $name = $item['name'] ?? 'Product';
    $qty = $item['quantity'] ?? 1;
    $price = $item['price'] ?? 0;
    $line_total = $qty * $price;
    $pdf->Cell(80,10,$name,1);
    $pdf->Cell(30,10,$qty,1,0,'C');
    $pdf->Cell(40,10,number_format($price,2),1,0,'R');
    $pdf->Cell(40,10,number_format($line_total,2),1,1,'R');
}
$pdf->SetFont('Arial','B',12);
$pdf->Cell(150,10,'Subtotal',1,0,'R');
$pdf->Cell(40,10,number_format($total,2),1,1,'R');
$pdf->Cell(150,10,'Shipping',1,0,'R');
$pdf->Cell(40,10,number_format($shipping,2),1,1,'R');
$pdf->Cell(150,10,'Grand Total',1,0,'R');
$pdf->Cell(40,10,number_format($grandTotal,2),1,1,'R');
$pdf->Ln(15);
$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(102,16,242);
$pdf->Cell(0,10,'Thank you for shopping with MiniShop!',0,1,'C');
$pdf->Output('D', 'Invoice_'.$order_id.'.pdf');
exit; 
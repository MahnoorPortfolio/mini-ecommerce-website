<?php
session_start();

function formatPrice($price){return '$'.number_format($price,2);} // simple formatter

function cartCount(){return isset($_SESSION['cart'])?array_sum(array_column($_SESSION['cart'],'quantity')):0;}

function addToCart($id,$qty=1){
    if($qty<1)$qty=1;
    if(!isset($_SESSION['cart']))$_SESSION['cart']=[];
    foreach($_SESSION['cart'] as &$item){
        if($item['product_id']===$id){$item['quantity']+=$qty;return;}
    }
    $_SESSION['cart'][]=['product_id'=>$id,'quantity'=>$qty];
}

function removeFromCart($id){if(isset($_SESSION['cart'])){$_SESSION['cart']=array_values(array_filter($_SESSION['cart'],fn($i)=>$i['product_id']!=$id));}}

function updateCartQuantity($id,$qty){if($qty<1){removeFromCart($id);return;}if(isset($_SESSION['cart'])){foreach($_SESSION['cart'] as &$i){if($i['product_id']===$id){$i['quantity']=$qty;break;}}}}

function cartTotal($conn){$total=0;if(!isset($_SESSION['cart']))return $total; $ids=array_column($_SESSION['cart'],'product_id'); if(!$ids)return 0; $placeholders=implode(',',array_fill(0,count($ids),'?')); $types=str_repeat('i',count($ids)); $stmt=$conn->prepare("SELECT id,price FROM products WHERE id IN ($placeholders)"); $stmt->bind_param($types,...$ids); $stmt->execute(); $res=$stmt->get_result(); $prices=[]; while($row=$res->fetch_assoc()){$prices[$row['id']]=$row['price'];} foreach($_SESSION['cart'] as $item){$total+=($prices[$item['product_id']]??0)*$item['quantity'];} return $total;}

// Ensure the products table exists
function ensureProductsTable($conn){
    $conn->query("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        image VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Import products from folders under a base directory (one folder = category)
function importProductsFromFolders($conn,$baseDir){
    ensureProductsTable($conn);
    // If we already have products, assume import done
    $check = $conn->query('SELECT COUNT(*) as c FROM products');
    if($check && $check->fetch_assoc()['c'] > 0) return 0;
    if(!is_dir($baseDir))return 0;
    $inserted=0;
    $allowedExt=['jpg','jpeg','png','webp'];
    // Beautiful names and descriptions for demo categories
    $prettyNames = [
        'Men Shirts' => [
            ['Classic Oxford Shirt', 'A timeless classic, perfect for both business and casual occasions.'],
            ['Slim Fit Linen Shirt', 'Stay cool and stylish with this breathable linen shirt.'],
            ['Checked Flannel Shirt', 'Soft, warm, and ideal for layering in cooler weather.'],
            ['Denim Button-Down', 'Rugged denim with a modern cut for everyday wear.'],
            ['Elegant White Shirt', 'Crisp, clean, and always in style.'],
            ['Striped Business Shirt', 'Sharp stripes for a confident, professional look.'],
            ['Casual Chambray Shirt', 'Lightweight and versatile for any outing.'],
            ['Short Sleeve Summer Shirt', 'Beat the heat with this breezy, short-sleeve design.'],
        ],
        'Men Shoes' => [
            ['Leather Oxford Shoes', 'Premium leather shoes for formal events and business meetings.'],
            ['Casual Sneakers', 'Comfortable and stylish sneakers for everyday adventures.'],
            ['Classic Loafers', 'Slip-on loafers that blend comfort and elegance.'],
            ['Suede Derby Shoes', 'Soft suede with a modern derby silhouette.'],
            ['Sporty Running Shoes', 'Engineered for performance and all-day comfort.'],
            ['Chelsea Boots', 'Iconic boots for a bold, fashionable statement.'],
            ['Canvas Trainers', 'Lightweight trainers for a laid-back look.'],
            ['High-Top Sneakers', 'Trendy high-tops for a street-smart vibe.'],
        ],
        'Girls Shoes' => [
            ['Sparkle Ballet Flats', 'Adorable flats with a touch of sparkle for special occasions.'],
            ['Rainbow Sneakers', 'Colorful sneakers that brighten every step.'],
            ['Classic Mary Janes', 'Timeless style with a modern twist.'],
            ['Glitter Party Shoes', 'Perfect for parties and celebrations.'],
            ['Comfy Slip-Ons', 'Easy to wear and super comfortable.'],
            ['Cute Bow Sandals', 'Pretty sandals with a bow accent.'],
            ['Winter Fur Boots', 'Keep little feet warm and stylish.'],
            ['Flower Print Shoes', 'Fun floral prints for sunny days.'],
        ],
        'Girls Jewlery' => [
            ['Unicorn Pendant Necklace', 'A magical unicorn pendant for a touch of whimsy.'],
            ['Rainbow Bead Bracelet', 'Bright beads for a cheerful look.'],
            ['Heart Stud Earrings', 'Sweet heart-shaped studs for everyday sparkle.'],
            ['Butterfly Charm Anklet', 'Delicate anklet with butterfly charms.'],
            ['Star Drop Earrings', 'Shining stars for a dreamy style.'],
            ['Pearl Princess Necklace', 'Classic pearls for a touch of elegance.'],
            ['Flower Hair Clips', 'Pretty clips to complete any hairstyle.'],
            ['Friendship Bracelets', 'Shareable bracelets for best friends.'],
        ],
        'Boys Shoes' => [
            ['Sporty Kids Sneakers', 'Durable sneakers for active boys.'],
            ['Adventure Sandals', 'Perfect for summer adventures and play.'],
            ['Classic School Shoes', 'Smart and comfortable for school days.'],
            ['Winter Boots', 'Keep feet warm and dry in winter weather.'],
            ['Slip-On Canvas Shoes', 'Easy to wear and stylish for any outfit.'],
            ['Colorful Trainers', 'Bright colors for energetic kids.'],
            ['Waterproof Rain Boots', 'Splash in puddles with confidence!'],
            ['Light-Up Sneakers', 'Fun shoes that light up every step.'],
        ],
        'Boys Shirts' => [
            ['Plaid Button-Up Shirt', 'Classic plaid for a cool, casual look.'],
            ['Graphic Tee', 'Fun graphics for everyday style.'],
            ['Denim Shirt', 'Rugged denim for all occasions.'],
            ['Short Sleeve Polo', 'Smart and comfy for school or play.'],
            ['Long Sleeve Henley', 'Soft and stylish for cooler days.'],
            ['Striped Tee', 'Bold stripes for a playful vibe.'],
            ['Hooded Shirt', 'A shirt with a hood for extra style.'],
            ['Printed Vacation Shirt', 'Tropical prints for summer fun.'],
        ],
        'Women Bags' => [
            ['Elegant Leather Tote', 'Spacious and stylish for work or travel.'],
            ['Chic Crossbody Bag', 'Hands-free convenience with modern flair.'],
            ['Classic Shoulder Bag', 'Timeless design for any occasion.'],
            ['Mini Satchel', 'Compact and cute for essentials.'],
            ['Trendy Backpack', 'Fashion meets function in this backpack.'],
            ['Evening Clutch', 'Perfect for parties and special events.'],
            ['Woven Beach Bag', 'Carry your beach essentials in style.'],
            ['Quilted Handbag', 'Soft, quilted texture for a luxe look.'],
        ],
        'Women Dresses' => [
            ['Floral Maxi Dress', 'Flowy and feminine for sunny days.'],
            ['Little Black Dress', 'A must-have classic for every wardrobe.'],
            ['Boho Midi Dress', 'Relaxed fit with bohemian charm.'],
            ['Elegant Evening Gown', 'Make a statement at any event.'],
            ['Casual T-Shirt Dress', 'Effortless style for everyday wear.'],
            ['Wrap Dress', 'Flattering wrap design for all shapes.'],
            ['Summer Sundress', 'Lightweight and breezy for hot days.'],
            ['Denim Shirt Dress', 'Casual and cool for any outing.'],
        ],
        'Watches' => [
            ['Classic Leather Watch', 'Timeless design with a genuine leather strap.'],
            ['Sport Digital Watch', 'Water-resistant and perfect for workouts.'],
            ['Elegant Gold Watch', 'Add a touch of luxury to your wrist.'],
            ['Minimalist Silver Watch', 'Sleek and modern for any outfit.'],
            ['Smart Fitness Tracker', 'Track your steps and stay healthy.'],
            ['Kids Cartoon Watch', 'Fun designs for little ones learning time.'],
            ['Diver\'s Watch', 'Built for adventure above and below water.'],
            ['Rose Gold Bracelet Watch', 'Trendy and feminine for every day.'],
        ],
        'Sunglasses' => [
            ['Aviator Sunglasses', 'Iconic aviator style for a cool look.'],
            ['Round Retro Shades', 'Vintage-inspired round frames.'],
            ['Classic Wayfarers', 'Timeless design for any face shape.'],
            ['Sport Wrap Sunglasses', 'Stay protected during outdoor activities.'],
            ['Cat Eye Sunglasses', 'Bold and stylish for fashion lovers.'],
            ['Kids Colorful Shades', 'Fun colors for sunny play days.'],
            ['Oversized Glam Sunglasses', 'Make a statement with big frames.'],
            ['Polarized Driving Glasses', 'Reduce glare for safer driving.'],
        ],
        'Electronics' => [
            ['Wireless Earbuds', 'Enjoy music on the go with no wires.'],
            ['Smartphone', 'Latest model with stunning display.'],
            ['Bluetooth Speaker', 'Portable sound for every occasion.'],
            ['Fitness Tracker', 'Monitor your health and activity.'],
            ['Tablet', 'Lightweight and powerful for work or play.'],
            ['Smartwatch', 'Stay connected from your wrist.'],
            ['Digital Camera', 'Capture memories in high resolution.'],
            ['Portable Charger', 'Keep your devices powered up anywhere.'],
        ],
    ];
    $iterator = new DirectoryIterator($baseDir);
    foreach($iterator as $categoryInfo){
        if($categoryInfo->isDot() || !$categoryInfo->isDir()) continue;
        $category = $categoryInfo->getFilename();
        $catDir   = $categoryInfo->getPathname();
        $prettyList = $prettyNames[$category] ?? [];
        $prettyCount = count($prettyList);
        $i = 0;
        foreach(new DirectoryIterator($catDir) as $fileInfo){
            if($fileInfo->isDot() || !$fileInfo->isFile()) continue;
            $ext = strtolower($fileInfo->getExtension());
            if(!in_array($ext,$allowedExt)) continue;
            $imageRel = 'assets/images/'.rawurlencode($category).'/'.rawurlencode($fileInfo->getFilename());
            // Skip if already exists
            $stmt = $conn->prepare('SELECT id FROM products WHERE image = ? LIMIT 1');
            $stmt->bind_param('s',$imageRel);
            $stmt->execute();
            if($stmt->get_result()->num_rows) continue;
            // Assign beautiful name/desc if available, else fallback
            if($prettyCount > 0){
                $pick = $prettyList[$i % $prettyCount];
                $name = $pick[0];
                $desc = $pick[1];
                $i++;
            } else {
                $name = ucwords(str_replace(['-','_'], ' ', pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME)));
                $desc = 'A stylish and high-quality product from MiniShop.';
            }
            // Simple price generator
            $price = rand(10,120) + 0.99;
            $ins = $conn->prepare('INSERT INTO products(name,price,image,category,description) VALUES (?,?,?,?,?)');
            $ins->bind_param('sdsss',$name,$price,$imageRel,$category,$desc);
            if($ins->execute()) $inserted++;
        }
    }
    return $inserted;
}

// Wishlist functions
function addToWishlist($conn, $product_id) {
    if (!isset($_SESSION['user_id'])) return false;
    $user_id = $_SESSION['user_id'];
    $conn->query("CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY (user_id, product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $stmt = $conn->prepare('INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $user_id, $product_id);
    return $stmt->execute();
}
function removeFromWishlist($conn, $product_id) {
    if (!isset($_SESSION['user_id'])) return false;
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
    $stmt->bind_param('ii', $user_id, $product_id);
    return $stmt->execute();
}
function isInWishlist($conn, $product_id) {
    if (!isset($_SESSION['user_id'])) return false;
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1');
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows > 0;
}
function getWishlistProducts($conn) {
    if (!isset($_SESSION['user_id'])) return [];
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT p.* FROM products p JOIN wishlist w ON p.id = w.product_id WHERE w.user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $products = [];
    while ($row = $res->fetch_assoc()) $products[] = $row;
    return $products;
}

// Review functions
function addOrUpdateReview($conn, $product_id, $rating, $review) {
    if (!isset($_SESSION['user_id'])) return false;
    $user_id = $_SESSION['user_id'];
    $conn->query("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
        review TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY (user_id, product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    // Upsert
    $stmt = $conn->prepare('INSERT INTO reviews (user_id, product_id, rating, review) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating=VALUES(rating), review=VALUES(review), created_at=NOW()');
    $stmt->bind_param('iiis', $user_id, $product_id, $rating, $review);
    return $stmt->execute();
}
function getProductReviews($conn, $product_id) {
    $stmt = $conn->prepare('SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $reviews = [];
    while ($row = $res->fetch_assoc()) $reviews[] = $row;
    return $reviews;
}
function getProductRatingStats($conn, $product_id) {
    $stmt = $conn->prepare('SELECT COUNT(*) as count, AVG(rating) as avg_rating FROM reviews WHERE product_id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}
function getUserReviewForProduct($conn, $product_id) {
    if (!isset($_SESSION['user_id'])) return null;
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT * FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1');
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}
function deleteReview($conn, $review_id) {
    if (!isset($_SESSION['user_id'])) return false;
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare('DELETE FROM reviews WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $review_id, $user_id);
    return $stmt->execute();
}
function updateReview($conn, $review_id, $rating, $review) {
    if (!isset($_SESSION['user_id'])) return false;
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare('UPDATE reviews SET rating = ?, review = ?, created_at = NOW() WHERE id = ? AND user_id = ?');
    $stmt->bind_param('isii', $rating, $review, $review_id, $user_id);
    return $stmt->execute();
}
?> 
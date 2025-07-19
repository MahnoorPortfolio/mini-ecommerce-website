<?php
require_once __DIR__.'/includes/header.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])){
    header('Location: index.php');
    exit;
}

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if(!$username || !$password){
        $errors[] = 'Please fill in both fields.';
    } else {
        // Ensure users table exists
        $conn->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE,
            email VARCHAR(100) UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $stmt = $conn->prepare('SELECT id,username,password FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->bind_param('ss',$username,$username);
        $stmt->execute();
        $res = $stmt->get_result();
        if($user = $res->fetch_assoc()){
            if(password_verify($password,$user['password'])){
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username']= $user['username'];
                header('Location: index.php');
                exit;
            }
        }
        $errors[] = 'Invalid credentials.';
    }
}
?>
<div class="container py-5" style="max-width:480px;">
    <h1 class="section-title text-center" data-sr>Login</h1>

    <?php if($errors): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm" data-sr>
        <div class="mb-3">
            <label class="form-label">Username or Email</label>
            <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-gradient w-100">Login</button>
        <p class="mt-3 mb-0 text-center">Don't have an account? <a href="register.php">Register</a></p>
    </form>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?> 
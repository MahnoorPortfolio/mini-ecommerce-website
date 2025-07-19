<?php
require_once __DIR__.'/includes/header.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])){
    header('Location: index.php');
    exit;
}

$errors = [];
$success = false;
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Basic validation
    if(!$username || !$email || !$password || !$confirm){
        $errors[] = 'All fields are required.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = 'Invalid email address.';
    } elseif($password !== $confirm){
        $errors[] = 'Passwords do not match.';
    }

    if(!$errors){
        // Ensure users table exists
        $conn->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE,
            email VARCHAR(100) UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Check duplicates
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->bind_param('ss',$username,$email);
        $stmt->execute();
        if($stmt->get_result()->num_rows){
            $errors[] = 'Username or email already taken.';
        } else {
            $hash = password_hash($password,PASSWORD_DEFAULT);
            $ins = $conn->prepare('INSERT INTO users(username,email,password) VALUES (?,?,?)');
            $ins->bind_param('sss',$username,$email,$hash);
            if($ins->execute()){
                $success = true;
                $_SESSION['user_id'] = $ins->insert_id;
                $_SESSION['username']= $username;
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<div class="container py-5" style="max-width:540px;">
    <h1 class="section-title text-center" data-sr>Register</h1>

    <?php if($errors): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm" data-sr>
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button class="btn btn-gradient w-100">Register</button>
        <p class="mt-3 mb-0 text-center">Already have an account? <a href="login.php">Login</a></p>
    </form>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?> 
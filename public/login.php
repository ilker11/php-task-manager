<?php
session_start();
$success_message = '';

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']); // clear after showing once
}

require_once '../config/db.php';

$username = $password = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate
    if (empty($username) || empty($password)) {
        $errors[] = "Both fields are required.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;

            // Redirect to dashboard or home
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Login - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center" style="height: 100vh;">

    <div class="container" style="max-width: 400px;">
        <div class="card p-4 shadow-sm">
            <h3 class="text-center mb-4">Login</h3>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Log In</button>
            </form>

            <p class="text-center mt-3 mb-0">
                Don't have an account? <a href="register.php">Register</a>
            </p>
        </div>
    </div>

</body>

</html>
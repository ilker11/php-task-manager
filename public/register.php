<?php
require_once '../config/db.php';

$username = $password = $confirm_password = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Get form data
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 2. Validate input
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // 3. Check if username is already taken
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = "Username is already taken.";
        }
    }

    // 4. Insert new user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt->execute([$username, $hashed_password])) {
            session_start(); // Make sure session is started
            $_SESSION['success'] = "✅ Registration successful! Please log in.";
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Failed to register user.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center" style="height: 100vh;">

    <div class="container" style="max-width: 400px;">
        <div class="card p-4 shadow-sm">
            <h3 class="text-center mb-4">Register</h3>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Choose a username" required autofocus>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Choose a password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>

            <p class="text-center mt-3 mb-0">
                Already have an account? <a href="login.php">Login</a>
            </p>
        </div>
    </div>

</body>

</html>
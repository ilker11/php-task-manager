<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'] ?? null;

if (!$task_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch existing task
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
    echo "Task not found or you don't have permission.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_title = trim($_POST['title']);
    if (!empty($new_title)) {
        $stmt = $pdo->prepare("UPDATE tasks SET title = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_title, $task_id, $user_id]);
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Title cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Task - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center" style="height: 100vh;">

    <div class="container" style="max-width: 500px;">
        <div class="card p-4 shadow-sm">
            <h3 class="text-center mb-4">Edit Task</h3>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($task['title']) ?>" required>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>
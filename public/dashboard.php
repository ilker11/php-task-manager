<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle task addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    if (!empty($title)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title) VALUES (?, ?)");
        $stmt->execute([$user_id, $title]);
    }
}

// Handle task completion
if (isset($_GET['done'])) {
    $task_id = (int)$_GET['done'];
    $stmt = $pdo->prepare("UPDATE tasks SET is_done = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
}

// Handle task deletion
if (isset($_GET['delete'])) {
    $task_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
}

// Fetch tasks
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$user_id];

if ($filter === 'done') {
    $sql .= " AND is_done = 1";
} elseif ($filter === 'pending') {
    $sql .= " AND is_done = 0";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Your Tasks - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">Add New Task</h4>
                <form method="POST" action="dashboard.php" class="d-flex gap-2">
                    <input type="text" name="title" class="form-control" placeholder="What do you need to do?" required>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            </div>
        </div>

        <h5 class="mb-3">Filter Tasks:</h5>
        <div class="btn-group mb-4">
            <a href="dashboard.php?filter=all" class="btn btn-secondary <?= $filter === 'all' ? 'active' : '' ?>">All</a>
            <a href="dashboard.php?filter=pending" class="btn btn-secondary <?= $filter === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="dashboard.php?filter=done" class="btn btn-secondary <?= $filter === 'done' ? 'active' : '' ?>">Completed</a>
        </div>

        <ul class="list-group">
            <?php if (count($tasks) === 0): ?>
                <li class="list-group-item text-muted">No tasks found for this filter.</li>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center <?= $task['is_done'] ? 'text-decoration-line-through text-muted' : '' ?>">
                        <?= htmlspecialchars($task['title']) ?>
                        <div class="btn-group btn-group-sm">
                            <?php if (!$task['is_done']): ?>
                                <a href="?done=<?= $task['id'] ?>&filter=<?= htmlspecialchars($filter) ?>" class="btn btn-outline-success">✔</a>
                            <?php endif; ?>
                            <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-outline-primary">✏</a>
                            <a href="?delete=<?= $task['id'] ?>&filter=<?= htmlspecialchars($filter) ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this task?')">🗑</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

</body>

</html>
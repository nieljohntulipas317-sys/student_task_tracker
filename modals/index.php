<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/classses/task.php';

$db = database_connection();
$userId = require_login();
$taskObj = new Task($db, $userId);

$keyword = trim($_GET['keyword'] ?? '');

$tasks = $keyword !== ''
    ? $taskObj->searchTasks($keyword)
    : $taskObj->getTasks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Task Tracker</title>
    <link rel="stylesheet" href="../style.css?v=20261021-task-tracker">
</head>
<body>
    <div class="top-bar"></div>
    <main class="container">
        <header class="app-header">
            <div class="app-heading">
                <h1 class="page-title">Student Task Tracker</h1>
                <p>Organize your schoolwork. Stay on track. Get things done.</p>
            </div>
            <div class="user-menu">
                <span class="welcome-text">Welcome, <strong><?= e($_SESSION['user_name'] ?? 'Student') ?></strong></span>
                <form method="post" action="logout.php">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button class="btn-logout" type="submit">Log out</button>
                </form>
            </div>
        </header>

        <form class="task-search" method="get" action="index.php">
            <input
                type="text"
                name="keyword"
                placeholder="Search by title, subject, priority, or status"
                value="<?= e($keyword) ?>"
            >
            <button type="submit">Search</button>
        </form>

        <a class="add-task" href="add_task.php">+ Add New Task</a>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Deadline</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)): ?>
                        <tr>
                            <td colspan="7">No tasks found. Add a task to get started.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?= e($task['task_id']) ?></td>
                                <td><?= e($task['task_title']) ?></td>
                                <td><?= e($task['subject']) ?></td>
                                <td><?= e($task['deadline']) ?></td>
                                <td><?= e($task['priority']) ?></td>
                                <td><?= e($task['status']) ?></td>
                                <td class="actions">
                                    <a href="idet_task.php?id=<?= urlencode($task['task_id']) ?>">Edit / Update</a>
                                    <form method="post" action="delete_task.php" onsubmit="return confirm('Delete this task?')">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= e($task['task_id']) ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</body>
</html>
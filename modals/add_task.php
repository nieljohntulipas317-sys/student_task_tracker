<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/classses/task.php';

$db = database_connection();
$userId = require_login();
$taskObj = new Task($db, $userId);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $taskObj->task_title  = trim($_POST['task_title'] ?? '');
    $taskObj->subject     = trim($_POST['subject'] ?? '');
    $taskObj->description = trim($_POST['description'] ?? '');
    $taskObj->deadline    = $_POST['deadline'] ?? '';
    $taskObj->priority    = $_POST['priority'] ?? 'Medium';
    $taskObj->status      = $_POST['status'] ?? 'Pending';

    if ($taskObj->task_title === '' || $taskObj->subject === '' || $taskObj->deadline === '') {
        $message = "Please fill in the title, subject, and deadline.";
    } elseif (!in_array($taskObj->priority, ['Low', 'Medium', 'High'], true)
        || !in_array($taskObj->status, ['Pending', 'Completed', 'Overdue'], true)) {
        $message = "Please choose a valid priority and status.";
    } elseif ($taskObj->addTask()) {
        header("Location: index.php");
        exit();
    } else {
        $message = "Something went wrong while saving the task. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Task</title>
    <link rel="stylesheet" href="../style.css?v=20261019-task-overview">
</head>
<body>
    <h1>Add New Task</h1>
    <?php if ($message !== ''): ?>
        <p class="error"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form class="add-form" method="POST" action="add_task.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Title</label>
        <input type="text" name="task_title"
            value="<?= htmlspecialchars($_POST['task_title'] ?? '') ?>" required>

        <label>Subject</label>
        <input type="text" name="subject"
            value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>

        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

        <label>Deadline</label>
        <input type="date" name="deadline"
            value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>" required>

        <label>Priority</label>
        <select name="priority">
            <option value="Low">Low</option>
            <option value="Medium" selected>Medium</option>
            <option value="High">High</option>
        </select>

        <label>Status</label>
        <select name="status">
            <option value="Pending" selected>Pending</option>
            <option value="Completed">Completed</option>
            <option value="Overdue">Overdue</option>
        </select>

        <div class="form-buttons">
            <button type="submit">Save Task</button>
            <a href="index.php">Cancel</a>
        </div>
    </form>
</body>
</html>

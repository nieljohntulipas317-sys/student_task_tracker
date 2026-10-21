<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/classses/task.php';

$db = database_connection();
$userId = require_login();
$taskObj = new Task($db, $userId);

$id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['task_id']) ? $_POST['task_id'] : null);

if (!$id) {
    header("Location: index.php");
    exit();
}

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
        $record = $_POST;
    } elseif (!in_array($taskObj->priority, ['Low', 'Medium', 'High'], true)
        || !in_array($taskObj->status, ['Pending', 'Completed', 'Overdue'], true)) {
        $message = "Please choose a valid priority and status.";
        $record = $_POST;
    } elseif ($taskObj->updateTask($id)) {
        header("Location: index.php");
        exit();
    } else {
        $message = "Something went wrong while updating the task. Please try again.";
        $record = $_POST;
    }
} else {
    $record = $taskObj->getTaskById($id);
    if (!$record) {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <link rel="stylesheet" href="../style.css?v=20261019-task-overview">
</head>
<body>
    <div class="container">
        <h1 class="page-title">Edit Task</h1>

        <?php if ($message !== ''): ?>
            <p class="error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form class="task-form" method="POST" action="idet_task.php?id=<?php echo urlencode($id); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($id); ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="task_title" value="<?php echo htmlspecialchars($record['task_title'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" value="<?php echo htmlspecialchars($record['subject'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-group full-width">
                <label>Description</label>
                <textarea name="description"><?php echo htmlspecialchars($record['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" value="<?php echo htmlspecialchars($record['deadline'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority">
                        <?php foreach (['Low', 'Medium', 'High'] as $p): ?>
                            <option value="<?php echo $p; ?>" <?php echo (($record['priority'] ?? '') === $p) ? 'selected' : ''; ?>><?php echo $p; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['Pending', 'Completed', 'Overdue'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo (($record['status'] ?? '') === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-update">Update Task</button>
                <a href="index.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
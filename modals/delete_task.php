<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/classses/task.php';

$db = database_connection();
$userId = require_login();
$taskObj = new Task($db, $userId);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf_token($_POST['csrf_token'] ?? '');
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || !$taskObj->deleteTask($id)) {
    http_response_code(404);
    exit('Unable to delete that task.');
}

header("Location: index.php");
exit();
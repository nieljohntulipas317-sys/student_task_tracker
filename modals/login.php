<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/classses/account.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$account = new Account(database_connection());
$message = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $login = strtolower(trim($_POST['login'] ?? ''));
    $password = $_POST['password'] ?? '';
    $user = $login !== '' ? $account->findByLogin($login) : false;

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: index.php');
        exit();
    }

    $message = 'Invalid username/email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in | Student Task Tracker</title>
    <link rel="stylesheet" href="../style.css?v=20261021-login">
</head>
<body>
    <main class="container login-container">
        <section class="login-card" aria-labelledby="login-title">
            <h1 class="page-title" id="login-title">Log in</h1>
            <p>Sign in to manage your tasks.</p>
            <?php if ($message !== ''): ?><p class="error" role="alert"><?= e($message) ?></p><?php endif; ?>
            <form class="task-form login-form" method="post" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="form-group">
                    <label for="login">Username or email</label>
                    <input id="login" type="text" name="login" value="<?= e($login) ?>" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                </div>
                <div class="form-actions">
                    <button class="btn-update" type="submit">Log in</button>
                </div>
            </form>
            <p class="login-footer">Don't have an account? <a href="register.php">Create an account</a></p>
        </section>
    </main>
</body>
</html>

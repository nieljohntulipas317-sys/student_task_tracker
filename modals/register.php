<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/classses/account.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$account = new Account(database_connection());
$message = '';
$firstName = '';
$lastName = '';
$email = '';
$gender = '';
$address = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $gender = $_POST['gender'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $name = trim($firstName . ' ' . $lastName);
    $validGenders = ['female', 'male', 'other', 'prefer_not_to_say'];

    if ($firstName === '' || $lastName === '' || strlen($firstName) > 50
        || strlen($lastName) > 50 || strlen($name) > 100
        || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Enter valid first name, last name, and email address.';
    } elseif (!in_array($gender, $validGenders, true) || $address === '' || strlen($address) > 255) {
        $message = 'Select a gender and enter an address of 255 characters or fewer.';
    } elseif (!preg_match('/^[a-z0-9_.-]{3,50}$/', $username)) {
        $message = 'Username must be 3-50 characters and use only letters, numbers, dots, dashes, or underscores.';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $message = 'The passwords do not match.';
    } elseif ($account->findByEmail($email)) {
        $message = 'An account with that email already exists.';
    } elseif ($account->findByUsername($username)) {
        $message = 'That username is already taken.';
    } elseif ($account->register($name, $email, $username, $gender, $address, $password)) {
        $user = $account->findByLogin($email);
        if (!$user) {
            http_response_code(500);
            exit('Account was created, but could not be loaded. Please log in.');
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: index.php');
        exit();
    } else {
        $message = 'Unable to create the account. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account | Student Task Tracker</title>
    <link rel="stylesheet" href="../style.css?v=20261021-register">
</head>
<body>
    <main class="register-page">
        <section class="register-card" aria-labelledby="register-title">
            <header class="register-header">
                <h1 class="page-title" id="register-title">Create an account</h1>
                <p>Register as a library member</p>
            </header>
            <?php if ($message !== ''): ?><p class="error register-error" role="alert"><?= e($message) ?></p><?php endif; ?>
            <form class="register-form" method="post" action="register.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <h2 class="register-section-title">Your details</h2>
                <div class="register-field">
                    <label for="first_name">First Name</label>
                    <input id="first_name" type="text" name="first_name" maxlength="50" value="<?= e($firstName) ?>" required autocomplete="given-name" placeholder="Juan">
                </div>
                <div class="register-field">
                    <label for="last_name">Last Name</label>
                    <input id="last_name" type="text" name="last_name" maxlength="50" value="<?= e($lastName) ?>" required autocomplete="family-name" placeholder="Dela Cruz">
                </div>
                <div class="register-field register-field-wide">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" maxlength="254" value="<?= e($email) ?>" required autocomplete="email" placeholder="juan@email.com">
                </div>
                <div class="register-field">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="" disabled <?= $gender === '' ? 'selected' : '' ?>>Select</option>
                        <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="other" <?= $gender === 'other' ? 'selected' : '' ?>>Other</option>
                        <option value="prefer_not_to_say" <?= $gender === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
                    </select>
                </div>
                <div class="register-field">
                    <label for="address">Address</label>
                    <input id="address" type="text" name="address" maxlength="255" value="<?= e($address) ?>" required autocomplete="street-address" placeholder="Cebu City">
                </div>
                <h2 class="register-section-title register-login-title">Login details</h2>
                <div class="register-field register-field-wide">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" maxlength="50" minlength="3" pattern="[A-Za-z0-9_.-]{3,50}" value="<?= e($username) ?>" required autocomplete="username" placeholder="Choose a username">
                </div>
                <div class="register-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" minlength="8" required autocomplete="new-password" placeholder="Create a password">
                </div>
                <div class="register-field">
                    <label for="confirm_password">Confirm Password</label>
                    <input id="confirm_password" type="password" name="confirm_password" minlength="8" required autocomplete="new-password" placeholder="Re-enter password">
                </div>
                <div class="register-submit">
                    <button class="btn-update" type="submit">Create Account</button>
                </div>
            </form>
            <p class="register-footer">Already have an account? <a href="login.php">Log in</a></p>
        </section>
    </main>
</body>
</html>

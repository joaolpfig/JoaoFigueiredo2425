<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in both fields.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        $stmt = $liga->prepare("SELECT id_utilizador FROM utilizadores WHERE reset_token = ? AND reset_token_expira > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $liga->prepare("UPDATE utilizadores SET senha = ?, reset_token = NULL, reset_token_expira = NULL WHERE id_utilizador = ?");
            $stmt->bind_param("si", $hashed, $user['id_utilizador']);
            $stmt->execute();

            header("Location: login.php?success=password_updated");
            exit;
        } else {
            $message = "Invalid or expired token.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set New Password</title>
    <link rel="icon" href="img/IMAGENS PARA O ICON SITE/logoplug.jpg" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<div class="login-container">
    <h1>Set a New Password</h1>

    <?php if (!empty($message)): ?>
        <p style="color: red;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Update Password</button>
    </form>
</div>
</body>
</html>

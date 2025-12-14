<?php
session_start();
require "../config/db.php";

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password.";
    } else {
        // Fetch user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Plain-text password verification
            if ($password === $user['password']) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: ../index.php");
                exit;
            }
        }

        // If we reach here → login failed
        $error = "Invalid username or password.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign in — Library System</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <main class="login-wrapper" aria-labelledby="login-heading">
        <form class="login-card" method="POST" novalidate>
            <div class="brand-wrap">
                <div class="logo" aria-hidden="true">LS</div>
                <h1 id="login-heading" class="brand">Library System</h1>
            </div>

            <?php if($error): ?>
                <div class="error" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required autofocus autocomplete="username">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="password-row">
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                    <button type="button" class="show-password" aria-label="Toggle password visibility" onclick="togglePassword()">Show</button>
                </div>
            </div>

            <div class="actions">
                <button class="btn" type="submit">Sign In</button>
            </div>

            <p class="help">Need an account? Contact the administrator.</p>
        </form>
    </main>

    <script>
        function togglePassword(){
            const input = document.getElementById('password');
            const btn = document.querySelector('.show-password');
            if(input.type === 'password'){
                input.type = 'text';
                btn.textContent = 'Hide';
            } else {
                input.type = 'password';
                btn.textContent = 'Show';
            }
        }
    </script>
</body>
</html>

<?php
session_start();
require "../config/db.php";

/* =======================
   ACCESS CONTROL
======================= */
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';
$success = '';

/* =======================
   ADD USER
======================= */
if (isset($_POST['add'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role     = $_POST['role'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        try {
            // Plain-text password (no hashing)
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password, role)
                 VALUES (:u, :e, :p, :r)"
            );
            $stmt->execute([
                'u' => $username,
                'e' => $email,
                'p' => $password,  // plain-text
                'r' => $role
            ]);

            $success = "User added successfully.";
        } catch (PDOException $e) {
            $error = "Username or email already exists.";
        }
    }
}
?>
<?php

/* =======================
   DELETE USER
======================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    if ($id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: manage_users.php");
    exit;
}

/* =======================
   FETCH USERS
======================= */
$stmt = $pdo->prepare("SELECT id, username, email, role FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manage Users — Library System</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
</head>
<body>
    <div class="container">
        <div class="panel">
            <div class="panel-header">
                <h2 class="title">Manage Users</h2>
                <div class="controls">
                    <input type="search" id="user-q" class="search" placeholder="Search users">
                    <a href="../index.php" class="btn secondary">Home</a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="form-card" style="margin-bottom:16px">
                <form method="POST" class="book-form">
                    <div class="grid">
                        <div class="field">
                            <label for="username">Username</label>
                            <input id="username" name="username" type="text" placeholder="Username" required>
                        </div>

                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" placeholder="Email" required>
                        </div>

                        <div class="field">
                            <label for="password">Password</label>
                            <input id="password" name="password" type="password" placeholder="Password" required>
                        </div>

                        <div class="field">
                            <label for="role">Role</label>
                            <select id="role" name="role">
                                <option value="user">User</option>
                                <option value="administrator">Administrator</option>
                            </select>
                        </div>
                    </div>

                    <div class="actions" style="margin-top:8px">
                        <button type="submit" name="add" class="btn">Add User</button>
                    </div>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <?php foreach ($users as $i => $u): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['role']) ?></td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Delete this user?')" class="del">Delete</a>
                                <?php else: ?>
                                    (You)
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // simple client-side filter for users
        document.getElementById('user-q').addEventListener('input', function(e){
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('#users-tbody tr').forEach(r => {
                r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>
</body>
</html>

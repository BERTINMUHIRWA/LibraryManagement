<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "../auth/session_check.php";
require "../config/db.php";

$error = '';
$success = '';

/* =====================
   HANDLE FORM SUBMIT
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_POST['user_id'];
    $book_id    = $_POST['book_id'];
    $issue_date = $_POST['issue_date'];

    // Check book availability
    $stmt = $pdo->prepare("SELECT status FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        $error = "Book not found.";
    } elseif ($book['status'] !== 'available') {
        $error = "Book is already issued.";
    } else {
        try {
            $pdo->beginTransaction();

            // Insert transaction
            $stmt = $pdo->prepare(
                "INSERT INTO transactions (user_id, book_id, issue_date, status)
                 VALUES (?, ?, ?, 'issued')"
            );
            $stmt->execute([$user_id, $book_id, $issue_date]);

            // Update book status
            $stmt = $pdo->prepare(
                "UPDATE books SET status = 'issued' WHERE id = ?"
            );
            $stmt->execute([$book_id]);

            $pdo->commit();
            $success = "Book issued successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Database error.";
        }
    }
}

/* =====================
   LOAD USERS & BOOKS
===================== */
$users = $pdo->query("SELECT id, username FROM users")->fetchAll();
$books = $pdo->query("SELECT id, title FROM books WHERE status='available'")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Issue Book | Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="container">
    <h2>📚 Issue Book</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>User</label>
        <select name="user_id" required>
            <option value="">-- Select User --</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>">
                    <?= htmlspecialchars($u['username']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Book</label>
        <select name="book_id" required>
            <option value="">-- Select Book --</option>
            <?php foreach ($books as $b): ?>
                <option value="<?= $b['id'] ?>">
                    <?= htmlspecialchars($b['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Issue Date</label>
        <input type="date" name="issue_date" required>

        <button type="submit">Issue Book</button>
    </form>
</div>

</body>
</html>

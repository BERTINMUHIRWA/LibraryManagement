<?php
session_start();
require "../config/db.php";

// 🔐 Protect page (login required)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch transactions with related user & book
try {
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            u.username,
            b.title,
            t.borrow_date,
            t.return_date,
            t.status
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN books b ON t.book_id = b.id
        ORDER BY t.borrow_date DESC
    ");
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error loading transactions.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Transactions | Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="container">
    <h2>📄 Borrow / Return Transactions</h2>

    <?php if (count($transactions) === 0): ?>
        <p>No transactions found.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Book</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $i => $t): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($t['username']) ?></td>
                        <td><?= htmlspecialchars($t['title']) ?></td>
                        <td><?= htmlspecialchars($t['borrow_date']) ?></td>
                        <td>
                            <?= $t['return_date'] 
                                ? htmlspecialchars($t['return_date']) 
                                : '<em>Not returned</em>' ?>
                        </td>
                        <td>
                            <?= $t['status'] === 'returned' 
                                ? '✅ Returned' 
                                : '📚 Borrowed' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>

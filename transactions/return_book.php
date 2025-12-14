<?php
require "../auth/session_check.php";
require "../config/db.php";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $return_date = $_POST['return_date'];

    // Get transaction
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = :id AND status='issued'");
    $stmt->execute([':id'=>$transaction_id]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        $error = "Transaction not found or already returned.";
    } else {
        try {
            $pdo->beginTransaction();

            // Update transaction
            $stmt = $pdo->prepare("UPDATE transactions SET status='returned', return_date=:return_date WHERE id=:id");
            $stmt->execute([
                ':return_date'=>$return_date,
                ':id'=>$transaction_id
            ]);

            // Update book status
            $stmt = $pdo->prepare("UPDATE books SET status='available' WHERE id=:book_id");
            $stmt->execute([':book_id'=>$transaction['book_id']]);

            $pdo->commit();
            $success = "Book returned successfully!";
        } catch(PDOException $e) {
            $pdo->rollBack();
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Return Book — Library Management</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
</head>
<body>
    <div class="container form-container">
        <div class="form-card">
            <div class="form-header">
                <div>
                    <h2 class="form-title">Return Book</h2>
                    <div class="form-sub">Record the return of an issued book by transaction ID.</div>
                </div>
                <div class="form-actions">
                    <a href="../index.php" class="btn-secondary">Home</a>
                    <a href="view_transactions.php" class="btn-secondary">View Transactions</a>
                </div>
            </div>

            <?php if($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" class="book-form">
                <div class="grid">
                    <div class="field">
                        <label for="transaction_id">Transaction ID</label>
                        <input id="transaction_id" name="transaction_id" type="text" value="<?php echo isset($transaction_id) ? htmlspecialchars($transaction_id) : ''; ?>" required>
                    </div>

                    <div class="field">
                        <label for="return_date">Return Date</label>
                        <input id="return_date" name="return_date" type="date" value="<?php echo isset($return_date) ? htmlspecialchars($return_date) : date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Return Book</button>
                    <a href="view_transactions.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
require "auth/session_check.php";
?>

<?php
// gather dashboard counts
require "config/db.php";

$counts = [
    'books' => 0,
    'issued' => 0,
    'users' => 0,
    'transactions' => 0,
];

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM books");
    $counts['books'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM books WHERE status='issued'");
    $counts['issued'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $counts['users'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM transactions");
    $counts['transactions'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT t.id, t.issue_date, t.return_date, t.status, b.title, u.username
                         FROM transactions t
                         LEFT JOIN books b ON b.id = t.book_id
                         LEFT JOIN users u ON u.id = t.user_id
                         ORDER BY t.id DESC LIMIT 8");
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recent = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library Dashboard</title>
    <link rel="stylesheet" href="assets/css/vars.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="brand-wrap">
                <div class="logo" aria-hidden="true">LS</div>
                <h1 class="site-title">Library Management</h1>
            </div>
            <div class="user-area">
                <span class="user-name">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a class="btn-logout" href="auth/logout.php">Logout</a>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- stats removed per request -->

        <section class="quick-actions">
            <a class="card" href="books/add_book.php">
                <div class="card-title">Add Book</div>
                <div class="card-desc">Add a new book to the catalogue</div>
            </a>
            <a class="card" href="books/view_books.php">
                <div class="card-title">View Books</div>
                <div class="card-desc">Browse and search all books</div>
            </a>
            <a class="card" href="transactions/issue_book.php">
                <div class="card-title">Issue Book</div>
                <div class="card-desc">Record a book issue to a user</div>
            </a>
            <a class="card" href="transactions/return_book.php">
                <div class="card-title">Return Book</div>
                <div class="card-desc">Process returned books</div>
            </a>
            <a class="card" href="users/manage_users.php">
                <div class="card-title">Manage Users</div>
                <div class="card-desc">Create or update user accounts</div>
            </a>
        </section>

        <section class="notes">
            <h2>Quick Tips</h2>
            <ul>
                <li>Use "View Books" to search and edit book records.</li>
                <li>Issue books only to registered users.</li>
                <li>Contact the system administrator to create new librarian accounts.</li>
            </ul>
        </section>

        <!-- recent transactions panel removed per request -->
    </main>

    <footer class="site-footer">
        <div class="container">&copy; <?= date('Y') ?> Library Management</div>
    </footer>
</body>
</html>

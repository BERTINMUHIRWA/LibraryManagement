<?php
require "../auth/session_check.php";
require "../config/db.php";

$stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC");
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Books — Library Management</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="container">
        <div class="panel">
            <div class="panel-header">
                <h2 class="title">Books</h2>
                <div class="controls">
                    <input type="search" id="q" class="search" placeholder="Search title, author, or ISBN">
                    <a href="../index.php" class="btn secondary">Home</a>
                    <a href="add_book.php" class="btn">Add Book</a>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Published</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="books-tbody">
                    <?php foreach($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['id']) ?></td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['isbn']) ?></td>
                            <td><?= htmlspecialchars($book['published_year']) ?></td>
                            <td><span class="status <?= $book['status'] === 'available' ? 'available' : 'checked' ?>"><?= htmlspecialchars($book['status']) ?></span></td>
                            <td class="actions">
                                <a href="edit_book.php?id=<?= $book['id'] ?>">Edit</a>
                                <a href="delete_book.php?id=<?= $book['id'] ?>" class="del" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Simple client-side search filter
        document.getElementById('q').addEventListener('input', function(e){
            const q = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#books-tbody tr');
            rows.forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = text.indexOf(q) > -1 ? '' : 'none';
            });
        });
    </script>
</body>
</html>

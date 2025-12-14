<?php
require "../auth/session_check.php";
require "../config/db.php";

$error = '';
$success = '';

// Get book ID from GET
$book_id = $_GET['id'] ?? null;

if (!$book_id) {
    echo "No book selected.";
    exit;
}

// Fetch existing book data
$stmt = $pdo->prepare("SELECT * FROM books WHERE id=:id");
$stmt->execute([':id'=>$book_id]);
$book = $stmt->fetch();

if (!$book) {
    echo "Book not found.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $year = trim($_POST['published_year']);

    // Validation
    if (empty($title) || empty($author) || empty($isbn)) {
        $error = "Title, Author, and ISBN are required.";
    } elseif (!preg_match("/^[0-9A-Za-z-]{10,20}$/", $isbn)) {
        $error = "Invalid ISBN format.";
    } elseif ($year && !preg_match("/^\d{4}$/", $year)) {
        $error = "Published year must be 4 digits.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE books 
                                   SET title=:title, author=:author, isbn=:isbn, published_year=:year
                                   WHERE id=:id");
            $stmt->execute([
                ':title'=>$title,
                ':author'=>$author,
                ':isbn'=>$isbn,
                ':year'=>$year ?: null,
                ':id'=>$book_id
            ]);
            $success = "Book updated successfully!";
            // Refresh book data
            $stmt = $pdo->prepare("SELECT * FROM books WHERE id=:id");
            $stmt->execute([':id'=>$book_id]);
            $book = $stmt->fetch();
        } catch(PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = "ISBN already exists.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Book — Library Management</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
</head>
<body>
    <div class="container form-container">
        <div class="form-card">
            <div class="form-header">
                <div>
                    <h2 class="form-title">Edit Book</h2>
                    <div class="form-sub">Modify book details. Changes are saved immediately on Update.</div>
                </div>
                <div class="form-actions">
                    <a href="view_books.php" class="btn-secondary">Back to Books</a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" class="book-form">
                <div class="grid">
                    <div class="field">
                        <label for="title">Title</label>
                        <input id="title" name="title" type="text" value="<?= htmlspecialchars($book['title']) ?>" required>
                    </div>

                    <div class="field">
                        <label for="author">Author</label>
                        <input id="author" name="author" type="text" value="<?= htmlspecialchars($book['author']) ?>" required>
                    </div>

                    <div class="field">
                        <label for="isbn">ISBN</label>
                        <input id="isbn" name="isbn" type="text" value="<?= htmlspecialchars($book['isbn']) ?>" required>
                    </div>

                    <div class="field">
                        <label for="published_year">Published Year</label>
                        <input id="published_year" name="published_year" type="text" value="<?= htmlspecialchars($book['published_year']) ?>">
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Update Book</button>
                    <a href="view_books.php" class="btn-secondary">Cancel</a>
                </div>
            </form>

            <p style="margin:0;margin-top:12px"><a href="view_books.php">Back to Books List</a></p>
        </div>
    </div>
</body>
</html>

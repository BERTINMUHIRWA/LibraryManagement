<?php
require "../auth/session_check.php";
require "../config/db.php";

$error = '';
$success = '';

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
        // Insert into DB
        try {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, published_year, status) 
                                   VALUES (:title, :author, :isbn, :year, 'available')");
            $stmt->execute([
                ':title' => $title,
                ':author' => $author,
                ':isbn' => $isbn,
                ':year' => $year ?: null
            ]);
            $success = "Book added successfully!";
        } catch (PDOException $e) {
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
    <title>Add Book — Library Management</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
</head>
<body>
    <div class="container form-container">
        <div class="form-card">
            <div class="form-header">
                <div>
                    <h2 class="form-title">Add New Book</h2>
                    <div class="form-sub">Create a new entry for the library catalogue.</div>
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
                        <input id="title" name="title" type="text" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                    </div>

                    <div class="field">
                        <label for="author">Author</label>
                        <input id="author" name="author" type="text" value="<?php echo isset($author) ? htmlspecialchars($author) : ''; ?>" required>
                    </div>

                    <div class="field">
                        <label for="isbn">ISBN</label>
                        <input id="isbn" name="isbn" type="text" value="<?php echo isset($isbn) ? htmlspecialchars($isbn) : ''; ?>" required>
                    </div>

                    <div class="field">
                        <label for="published_year">Published Year</label>
                        <input id="published_year" name="published_year" type="text" value="<?php echo isset($year) ? htmlspecialchars($year) : ''; ?>">
                    </div>

                    <div class="field full">
                        <label for="notes">Notes (optional)</label>
                        <textarea id="notes" name="notes" placeholder="Any additional information..."></textarea>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Add Book</button>
                    <a href="view_books.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

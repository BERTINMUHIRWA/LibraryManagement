<?php
require "../auth/session_check.php";
require "../config/db.php";

$book_id = $_GET['id'] ?? null;

if (!$book_id) {
    echo "No book selected.";
    exit;
}

try {
    // Optional: Check if the book is issued
    $stmt = $pdo->prepare("SELECT status FROM books WHERE id=:id");
    $stmt->execute([':id'=>$book_id]);
    $book = $stmt->fetch();

    if (!$book) {
        echo "Book not found.";
        exit;
    } elseif ($book['status'] == 'issued') {
        echo "Cannot delete a book that is currently issued.";
        exit;
    }

    // Delete the book
    $stmt = $pdo->prepare("DELETE FROM books WHERE id=:id");
    $stmt->execute([':id'=>$book_id]);

    header("Location: view_books.php");
    exit;
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}

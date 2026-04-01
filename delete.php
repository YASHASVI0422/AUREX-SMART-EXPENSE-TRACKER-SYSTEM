<?php
$conn = new mysqli("localhost", "root", "", "expense_tracker");

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: view.php?success=deleted");
$conn->close();
?>

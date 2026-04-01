<?php
$conn = new mysqli("localhost", "root", "", "expense_tracker");

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$id       = intval($_POST['id']);
$title    = $conn->real_escape_string(trim($_POST['title']));
$amount   = floatval($_POST['amount']);
$category = $conn->real_escape_string($_POST['category']);
$date     = $conn->real_escape_string($_POST['date']);

// Server-side validation
$errors = [];
if (empty($title))    $errors[] = "Title is required.";
if ($amount <= 0)     $errors[] = "Amount must be greater than zero.";
if (empty($date))     $errors[] = "Date is required.";
if ($date > date('Y-m-d')) $errors[] = "Date cannot be in the future.";

if (!empty($errors)) {
    $msg = urlencode(implode(" ", $errors));
    header("Location: edit.php?id=$id&error=$msg");
    exit;
}

$stmt = $conn->prepare("UPDATE expenses SET title=?, amount=?, category=?, date=? WHERE id=?");
$stmt->bind_param("sdssi", $title, $amount, $category, $date, $id);

if ($stmt->execute()) {
    header("Location: view.php?success=updated");
} else {
    header("Location: edit.php?id=$id&error=" . urlencode("Update failed."));
}

$conn->close();
?>

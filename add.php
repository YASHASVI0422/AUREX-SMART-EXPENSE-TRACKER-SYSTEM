<?php
$conn = new mysqli("localhost", "root", "", "expense_tracker");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$title    = $conn->real_escape_string($_POST['title']);
$amount   = $conn->real_escape_string($_POST['amount']);
$category = $conn->real_escape_string($_POST['category']);
$date     = $conn->real_escape_string($_POST['date']);

$sql = "INSERT INTO expenses (title, amount, category, date)
        VALUES ('$title', '$amount', '$category', '$date')";

if ($conn->query($sql) === TRUE) {
    header("Location: view.php");
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>

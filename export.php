<?php
// ── CSV Export — queries DB directly and streams as download ──
$conn = new mysqli("localhost", "root", "", "expense_tracker");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$filename = "aurex_expenses_" . date('Y-m-d') . ".csv";

// Set headers to force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// CSV column headers
fputcsv($output, ['ID', 'Title', 'Amount (INR)', 'Category', 'Date', 'Exported At']);

// Fetch all expenses
$result = $conn->query("SELECT id, title, amount, category, date FROM expenses ORDER BY date DESC");

$count = 0;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['title'],
            $row['amount'],
            $row['category'],
            $row['date'],
            date('Y-m-d H:i:s')
        ]);
        $count++;
    }
}

fclose($output);
$conn->close();
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Expense — Aurex</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "expense_tracker");

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: view.php"); exit; }

$stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$expense = $stmt->get_result()->fetch_assoc();

if (!$expense) { header("Location: view.php"); exit; }
?>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <div class="navbar-logo">💸</div>
    <span class="navbar-title">Aurex</span>
  </a>
  <div class="navbar-links">
    <a href="index.php">Home</a>
    <a href="view.php">Dashboard</a>
  </div>
</nav>

<section class="form-section">
  <div class="form-header">
    <div class="section-title">Edit Entry</div>
    <h2>Update Expense</h2>
    <p>Modify the details below and save your changes.</p>
  </div>

  <div class="form-card">
    <form action="update.php" method="POST">
      <input type="hidden" name="id" value="<?= $expense['id'] ?>"/>
      <div class="form-row">
        <div class="form-group">
          <label for="title">Expense Title</label>
          <input type="text" id="title" name="title"
                 value="<?= htmlspecialchars($expense['title']) ?>" required/>
        </div>
        <div class="form-group">
          <label for="amount">Amount (₹)</label>
          <input type="number" id="amount" name="amount"
                 value="<?= $expense['amount'] ?>" required/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="category">Category</label>
          <div class="select-wrapper">
            <select id="category" name="category">
              <?php
              $cats = ['Food'=>'🍔','Travel'=>'✈️','Shopping'=>'🛍️','Education'=>'📚'];
              foreach ($cats as $cat => $icon):
              ?>
                <option value="<?= $cat ?>" <?= $expense['category']===$cat?'selected':'' ?>>
                  <?= $icon ?> <?= $cat ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="date">Date</label>
          <input type="date" id="date" name="date"
                 value="<?= $expense['date'] ?>" required/>
        </div>
      </div>
      <div style="display:flex; gap:12px;">
        <button type="submit" class="submit-btn" style="flex:1;">
          <span>✏️ &nbsp; Save Changes</span>
        </button>
        <a href="view.php" style="
          flex:0.4; display:flex; align-items:center; justify-content:center;
          border:1px solid var(--border); border-radius:10px;
          color:var(--text-dim); text-decoration:none; font-size:14px;
          transition: all 0.2s;
        " onmouseover="this.style.borderColor='var(--border-bright)'"
           onmouseout="this.style.borderColor='var(--border)'">
          Cancel
        </a>
      </div>
    </form>
  </div>
</section>

<footer class="footer">
  <div class="footer-brand">Aurex</div>
  <div class="footer-text">Your financial data stays on your machine. Always.</div>
</footer>
</body>
</html>

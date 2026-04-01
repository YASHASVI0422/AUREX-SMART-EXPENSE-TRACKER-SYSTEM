<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Budget Limits — Aurex</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "expense_tracker");

// Handle form save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categories = ['Food','Travel','Shopping','Education'];
    foreach ($categories as $cat) {
        $limit = floatval($_POST['limit_'.$cat] ?? 0);
        // Upsert: update if exists, insert if not
        $stmt = $conn->prepare("
            INSERT INTO budget_limits (category, monthly_limit)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE monthly_limit = ?
        ");
        $stmt->bind_param("sdd", $cat, $limit, $limit);
        $stmt->execute();
    }
    header("Location: budgets.php?success=1");
    exit;
}

// Auto-create budget_limits table if it doesn't exist
$conn->query("
    CREATE TABLE IF NOT EXISTS budget_limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(50) NOT NULL UNIQUE,
        monthly_limit DECIMAL(10,2) NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

// Fetch current limits
$limits = [];
$res = $conn->query("SELECT category, monthly_limit FROM budget_limits");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $limits[$row['category']] = $row['monthly_limit'];
    }
}

// Fetch this month's spending per category
$month = date('Y-m');
$spent = [];
$res2 = $conn->query("
    SELECT category, SUM(amount) as total
    FROM expenses
    WHERE DATE_FORMAT(date, '%Y-%m') = '$month'
    GROUP BY category
");
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        $spent[$row['category']] = $row['total'];
    }
}

$categories = [
    'Food'      => '🍔',
    'Travel'    => '✈️',
    'Shopping'  => '🛍️',
    'Education' => '📚',
];
?>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <div class="navbar-logo">💸</div>
    <span class="navbar-title">Aurex</span>
  </a>
  <div class="navbar-links">
    <a href="index.php">Home</a>
    <a href="view.php">Dashboard</a>
    <a href="budgets.php" class="active">Budgets</a>
    <a href="monthly_report.php">Reports</a>
    <a href="history.php">History</a>
  </div>
</nav>

<section class="view-section">

  <?php if (isset($_GET['success'])): ?>
    <div class="toast toast-success">✓ Budget limits saved successfully.</div>
  <?php endif; ?>

  <div style="margin-bottom:40px;">
    <div class="section-title">Budget Control</div>
    <h1 style="font-family:'Playfair Display',serif; font-size:36px; font-weight:700; margin-top:8px;">
      Monthly Budget Limits
    </h1>
    <p style="color:var(--text-dim); font-size:15px; margin-top:8px; font-weight:300;">
      Set a spending cap for each category. Aurex will warn you when you're approaching or exceeding it.
    </p>
  </div>

  <!-- Budget Progress Cards -->
  <div class="budget-progress-grid">
    <?php foreach ($categories as $cat => $icon):
      $limit   = floatval($limits[$cat] ?? 0);
      $spentAmt = floatval($spent[$cat] ?? 0);
      $pct     = $limit > 0 ? min(100, round(($spentAmt / $limit) * 100)) : 0;
      $isOver  = $spentAmt > $limit && $limit > 0;
      $barColor = $pct >= 90 ? 'var(--danger)' : ($pct >= 65 ? '#e0a352' : 'var(--gold)');
    ?>
    <div class="budget-progress-card">
      <div class="bp-header">
        <div class="bp-icon"><?= $icon ?></div>
        <div>
          <div class="bp-cat"><?= $cat ?></div>
          <div class="bp-amounts">
            ₹<?= number_format($spentAmt, 0) ?>
            <?php if ($limit > 0): ?>
              <span style="color:var(--text-muted)"> / ₹<?= number_format($limit, 0) ?></span>
            <?php else: ?>
              <span style="color:var(--text-muted)"> spent (no limit set)</span>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($isOver): ?>
          <span class="badge danger" style="margin-left:auto;">Over!</span>
        <?php elseif ($pct >= 80 && $limit > 0): ?>
          <span class="badge" style="margin-left:auto; background:rgba(224,163,82,0.12); color:#e0a352; border:1px solid rgba(224,163,82,0.3);">Near limit</span>
        <?php endif; ?>
      </div>
      <?php if ($limit > 0): ?>
      <div class="bp-bar-track">
        <div class="bp-bar-fill" style="width:<?= $pct ?>%; background:<?= $barColor ?>;"></div>
      </div>
      <div style="font-size:12px; color:var(--text-muted); margin-top:6px; text-align:right;">
        <?= $pct ?>% used
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Set Limits Form -->
  <div class="form-card" style="margin-top:40px;">
    <div style="margin-bottom:30px;">
      <div class="section-title">Set Limits</div>
      <h3 style="font-family:'Cormorant Garamond',serif; font-size:26px; margin-top:6px;">
        Update Monthly Budgets
      </h3>
    </div>
    <form method="POST" action="budgets.php">
      <div class="form-row">
        <?php foreach ($categories as $cat => $icon): ?>
        <div class="form-group">
          <label><?= $icon ?> <?= $cat ?> Limit (₹)</label>
          <input type="number" name="limit_<?= $cat ?>"
                 value="<?= $limits[$cat] ?? '' ?>"
                 placeholder="e.g. 2000" min="0"/>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="submit-btn">
        <span>💾 &nbsp; Save Budget Limits</span>
      </button>
    </form>
  </div>

</section>

<footer class="footer">
  <div class="footer-brand">Aurex</div>
  <div class="footer-text">Your financial data stays on your machine. Always.</div>
</footer>
</body>
</html>

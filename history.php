<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Deletion History — Aurex</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "expense_tracker");

// Auto-create log table if somehow missing
$conn->query("
  CREATE TABLE IF NOT EXISTS expense_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_id INT,
    title VARCHAR(255),
    amount DECIMAL(10,2),
    category VARCHAR(50),
    action VARCHAR(20) DEFAULT 'DELETED',
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )
");

$logs = [];
$res  = $conn->query("SELECT * FROM expense_log ORDER BY logged_at DESC");
if ($res) {
    while ($r = $res->fetch_assoc()) $logs[] = $r;
}

$total_deleted = count($logs);
$total_value   = array_sum(array_column($logs, 'amount'));

$categoryIcons = ['Food'=>'🍔','Travel'=>'✈️','Shopping'=>'🛍️','Education'=>'📚'];
?>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <div class="navbar-logo">💸</div>
    <span class="navbar-title">Aurex</span>
  </a>
  <div class="navbar-links">
    <a href="index.php">Home</a>
    <a href="view.php">Dashboard</a>
    <a href="budgets.php">Budgets</a>
    <a href="monthly_report.php">Reports</a>
    <a href="history.php" class="active">History</a>
  </div>
</nav>

<section class="view-section">

  <!-- Header -->
  <div class="dash-header">
    <div>
      <div class="section-title">🔁 Trigger Log</div>
      <h1 class="dash-title">Deletion History</h1>
      <p class="dash-sub">
        Every deleted expense is automatically captured here by a
        <strong style="color:var(--gold)">MySQL BEFORE DELETE Trigger</strong>
        — no PHP code required.
      </p>
    </div>
  </div>

  <!-- DB Concept Banner -->
  <div style="
    background: rgba(201,168,76,0.06);
    border: 1px solid var(--border-bright);
    border-radius: var(--radius);
    padding: 18px 24px;
    margin-bottom: 28px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
  ">
    <div style="font-size:26px; flex-shrink:0;">⚡</div>
    <div>
      <div style="font-size:13px; font-weight:600; color:var(--gold); margin-bottom:4px; letter-spacing:0.05em;">
        HOW THIS WORKS — DATABASE TRIGGER
      </div>
      <div style="font-size:13px; color:var(--text-dim); line-height:1.7;">
        A <code style="color:var(--gold-light); background:var(--surface-3); padding:1px 6px; border-radius:4px;">BEFORE DELETE</code>
        trigger named <code style="color:var(--gold-light); background:var(--surface-3); padding:1px 6px; border-radius:4px;">before_expense_delete</code>
        fires automatically inside MySQL every time a row is deleted from the
        <code style="color:var(--gold-light); background:var(--surface-3); padding:1px 6px; border-radius:4px;">expenses</code> table.
        It copies the old row's data into <code style="color:var(--gold-light); background:var(--surface-3); padding:1px 6px; border-radius:4px;">expense_log</code>
        before the deletion happens — entirely at the database level.
      </div>
    </div>
  </div>

  <!-- Summary stats -->
  <div class="summary-cards" style="grid-template-columns: repeat(3,1fr); margin-bottom:32px;">
    <div class="summary-card" style="--card-accent:#e05252;">
      <div class="sc-icon">🗑</div>
      <div class="sc-body">
        <div class="sc-label">Total Deleted</div>
        <div class="sc-value"><?= $total_deleted ?></div>
        <div class="sc-sub">expenses removed</div>
      </div>
    </div>
    <div class="summary-card" style="--card-accent:#c9a84c;">
      <div class="sc-icon">💸</div>
      <div class="sc-body">
        <div class="sc-label">Total Value Lost</div>
        <div class="sc-value">₹<?= number_format($total_value, 2) ?></div>
        <div class="sc-sub">across all deletions</div>
      </div>
    </div>
    <div class="summary-card" style="--card-accent:#3b7dd8;">
      <div class="sc-icon">📋</div>
      <div class="sc-body">
        <div class="sc-label">Trigger Status</div>
        <div class="sc-value" style="font-size:18px; color:var(--success);">● Active</div>
        <div class="sc-sub">before_expense_delete</div>
      </div>
    </div>
  </div>

  <!-- Log table -->
  <?php if (empty($logs)): ?>
    <div style="text-align:center; padding:80px 20px;">
      <div style="font-size:48px; margin-bottom:16px;">📭</div>
      <div style="font-family:'Cormorant Garamond',serif; font-size:22px; color:var(--text-dim);">
        No deletions yet
      </div>
      <p style="font-size:14px; color:var(--text-muted); margin-top:8px;">
        When you delete an expense from the
        <a href="view.php" style="color:var(--gold); text-decoration:none;">dashboard</a>,
        it will appear here automatically.
      </p>
    </div>
  <?php else: ?>

    <div style="margin-bottom:16px;">
      <div class="section-title"><?= $total_deleted ?> log entries — captured by trigger</div>
    </div>

    <!-- Table header -->
    <div style="
      display:grid;
      grid-template-columns: 60px 1fr 120px 130px 160px 110px;
      gap:0;
      background:var(--surface-2);
      border:1px solid var(--border);
      border-radius:10px 10px 0 0;
      padding:12px 20px;
    ">
      <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em;">ID</div>
      <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em;">Expense Title</div>
      <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em;">Amount</div>
      <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em;">Category</div>
      <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em;">Deleted At</div>
      <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em;">Action</div>
    </div>

    <?php foreach ($logs as $i => $log):
      $icon = $categoryIcons[$log['category']] ?? '💳';
      $dt   = date('d M Y, h:i A', strtotime($log['logged_at']));
    ?>
    <div style="
      display:grid;
      grid-template-columns: 60px 1fr 120px 130px 160px 110px;
      gap:0;
      background:<?= $i%2===0 ? 'var(--surface)' : 'var(--surface-2)' ?>;
      border:1px solid var(--border);
      border-top:none;
      padding:14px 20px;
      align-items:center;
      <?= $i===count($logs)-1 ? 'border-radius:0 0 10px 10px;' : '' ?>
      transition: background 0.2s;
    " onmouseover="this.style.background='var(--surface-3)'"
       onmouseout="this.style.background='<?= $i%2===0 ? 'var(--surface)' : 'var(--surface-2)' ?>'">

      <div style="font-family:'Consolas',monospace; font-size:12px; color:var(--text-muted);">
        #<?= $log['expense_id'] ?>
      </div>
      <div style="font-size:14px; color:var(--text); font-weight:500;">
        <?= htmlspecialchars($log['title']) ?>
      </div>
      <div style="font-family:'Cormorant Garamond',serif; font-size:18px; color:var(--danger);">
        ₹<?= number_format($log['amount'], 2) ?>
      </div>
      <div style="font-size:13px; color:var(--text-dim);">
        <?= $icon ?> <?= htmlspecialchars($log['category']) ?>
      </div>
      <div style="font-size:12px; color:var(--text-muted);">
        <?= $dt ?>
      </div>
      <div>
        <span style="
          background:rgba(224,82,82,0.12);
          border:1px solid rgba(224,82,82,0.3);
          color:var(--danger);
          font-size:11px; font-weight:600;
          padding:4px 10px; border-radius:100px;
          letter-spacing:0.05em;
        "><?= $log['action'] ?></span>
      </div>
    </div>
    <?php endforeach; ?>

  <?php endif; ?>

  <div style="margin-top:40px; text-align:center;">
    <a href="view.php" class="btn-gold-outline">← Back to Dashboard</a>
  </div>

</section>

<footer class="footer">
  <div class="footer-brand">Aurex</div>
  <div class="footer-text">Your financial data stays on your machine. Always.</div>
</footer>

</body>
</html>

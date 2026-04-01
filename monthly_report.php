<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Monthly Report — Aurex</title>
  <link rel="stylesheet" href="style.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "expense_tracker");

// Auto-create view if missing
$conn->query("
  CREATE OR REPLACE VIEW monthly_summary AS
  SELECT
    DATE_FORMAT(date, '%Y-%m')   AS month,
    DATE_FORMAT(date, '%b %Y')   AS month_label,
    category,
    COUNT(*)                      AS total_entries,
    SUM(amount)                   AS total_spent,
    MAX(amount)                   AS highest_expense,
    ROUND(AVG(amount), 2)         AS avg_expense
  FROM expenses
  GROUP BY DATE_FORMAT(date, '%Y-%m'), category
  ORDER BY month DESC, total_spent DESC
");

// ── Query the VIEW ──
$viewRes = $conn->query("SELECT * FROM monthly_summary");
$viewRows = [];
if ($viewRes) {
    while ($r = $viewRes->fetch_assoc()) $viewRows[] = $r;
}

// ── Group by month for display ──
$byMonth = [];
foreach ($viewRows as $r) {
    $byMonth[$r['month']]['label']      = $r['month_label'];
    $byMonth[$r['month']]['categories'][] = $r;
    $byMonth[$r['month']]['total']       = ($byMonth[$r['month']]['total'] ?? 0) + $r['total_spent'];
}

// ── Chart data: top 3 months ──
$chartLabels = [];
$chartValues = [];
foreach (array_slice($byMonth, 0, 6, true) as $m => $data) {
    $chartLabels[] = $data['label'];
    $chartValues[] = round($data['total'], 2);
}
$chartLabels = array_reverse($chartLabels);
$chartValues = array_reverse($chartValues);

// ── Overall stats ──
$grandTotal  = array_sum(array_column($viewRows, 'total_spent'));
$grandAvg    = count($viewRows) > 0 ? round(array_sum(array_column($viewRows, 'avg_expense')) / count($viewRows), 2) : 0;
$grandMax    = count($viewRows) > 0 ? max(array_column($viewRows, 'highest_expense')) : 0;

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
    <a href="monthly_report.php" class="active">Reports</a>
    <a href="history.php">History</a>
  </div>
</nav>

<section class="view-section">

  <!-- Header -->
  <div class="dash-header">
    <div>
      <div class="section-title">📊 Database View</div>
      <h1 class="dash-title">Monthly Report</h1>
      <p class="dash-sub">
        This entire page is powered by a single query on a
        <strong style="color:var(--gold)">MySQL VIEW</strong>
        called <code style="color:var(--gold-light); background:var(--surface-3); padding:2px 8px; border-radius:4px; font-size:13px;">monthly_summary</code>
        — no complex PHP aggregation needed.
      </p>
    </div>
  </div>

  <!-- DB Concept Banner -->
  <div style="
    background: rgba(59,125,216,0.06);
    border: 1px solid rgba(59,125,216,0.3);
    border-radius: var(--radius);
    padding: 18px 24px;
    margin-bottom: 28px;
    display:flex; align-items:flex-start; gap:16px;
  ">
    <div style="font-size:26px; flex-shrink:0;">🔭</div>
    <div>
      <div style="font-size:13px; font-weight:600; color:#7AB3E8; margin-bottom:4px; letter-spacing:0.05em;">
        HOW THIS WORKS — DATABASE VIEW
      </div>
      <div style="font-size:13px; color:var(--text-dim); line-height:1.7;">
        A <strong>VIEW</strong> is a saved SQL query stored inside MySQL that acts like a virtual table.
        Instead of writing complex GROUP BY and aggregate queries in PHP every time,
        we query <code style="color:var(--gold-light); background:var(--surface-3); padding:1px 6px; border-radius:4px;">SELECT * FROM monthly_summary</code>
        and MySQL handles all the aggregation internally.
        The view uses <code style="color:var(--gold-light); background:var(--surface-3); padding:1px 6px; border-radius:4px;">COUNT(*)</code>,
        <code style="color:var(--gold-light); background:var(--surface-3); padding:1px 6px; border-radius:4px;">SUM()</code>,
        <code style="color:var(--gold-light); background:var(--surface-3); padding:1px 6px; border-radius:4px;">MAX()</code>,
        <code style="color:var(--gold-light); background:var(--surface-3); padding:1px 6px; border-radius:4px;">AVG()</code> and
        <code style="color:var(--gold-light); background:var(--surface-3); padding:1px 6px; border-radius:4px;">DATE_FORMAT()</code>.
      </div>
    </div>
  </div>

  <!-- Overall summary cards -->
  <div class="summary-cards" style="margin-bottom:32px;">
    <div class="summary-card" style="--card-accent:#c9a84c;">
      <div class="sc-icon">💰</div>
      <div class="sc-body">
        <div class="sc-label">Grand Total</div>
        <div class="sc-value">₹<?= number_format($grandTotal, 2) ?></div>
        <div class="sc-sub">all time spending</div>
      </div>
    </div>
    <div class="summary-card" style="--card-accent:#3b7dd8;">
      <div class="sc-icon">📈</div>
      <div class="sc-body">
        <div class="sc-label">Avg per Entry</div>
        <div class="sc-value">₹<?= number_format($grandAvg, 2) ?></div>
        <div class="sc-sub">across all categories</div>
      </div>
    </div>
    <div class="summary-card" style="--card-accent:#9b59b6;">
      <div class="sc-icon">🏆</div>
      <div class="sc-body">
        <div class="sc-label">Highest Single</div>
        <div class="sc-value">₹<?= number_format($grandMax, 2) ?></div>
        <div class="sc-sub">most expensive expense</div>
      </div>
    </div>
    <div class="summary-card" style="--card-accent:#1c8a5a;">
      <div class="sc-icon">📅</div>
      <div class="sc-body">
        <div class="sc-label">Months Tracked</div>
        <div class="sc-value"><?= count($byMonth) ?></div>
        <div class="sc-sub">months with expenses</div>
      </div>
    </div>
  </div>

  <!-- Trend chart -->
  <?php if (!empty($chartLabels)): ?>
  <div class="chart-card" style="margin-bottom:32px;">
    <div class="chart-title">
      <div class="section-title">From VIEW data</div>
      <h3>Monthly Spending Trend</h3>
    </div>
    <div class="chart-wrap" style="height:200px;">
      <canvas id="trendChart"></canvas>
    </div>
  </div>
  <?php endif; ?>

  <!-- Month-by-month breakdown -->
  <?php if (empty($byMonth)): ?>
    <div style="text-align:center; padding:80px 20px;">
      <div style="font-size:48px; margin-bottom:16px;">📭</div>
      <div style="font-family:'Cormorant Garamond',serif; font-size:22px; color:var(--text-dim);">No data yet</div>
      <p style="font-size:14px; color:var(--text-muted); margin-top:8px;">
        Add some expenses from the <a href="index.php" style="color:var(--gold); text-decoration:none;">home page</a>.
      </p>
    </div>
  <?php else: ?>

    <?php foreach ($byMonth as $month => $data): ?>
    <div style="margin-bottom:32px;">

      <!-- Month header -->
      <div style="
        display:flex; align-items:center; justify-content:space-between;
        background:var(--surface-2); border:1px solid var(--border);
        border-radius:10px 10px 0 0; padding:14px 22px;
      ">
        <div style="display:flex; align-items:center; gap:14px;">
          <div style="font-family:'Playfair Display',serif; font-size:20px; font-weight:700; color:var(--white);">
            <?= $data['label'] ?>
          </div>
          <span style="font-size:12px; color:var(--text-muted);">
            <?= count($data['categories']) ?> categor<?= count($data['categories'])>1?'ies':'y' ?>
          </span>
        </div>
        <div style="font-family:'Cormorant Garamond',serif; font-size:24px; font-weight:600; color:var(--gold-light);">
          ₹<?= number_format($data['total'], 2) ?>
        </div>
      </div>

      <!-- Category rows from VIEW -->
      <?php foreach ($data['categories'] as $ci => $cat):
        $icon = $categoryIcons[$cat['category']] ?? '💳';
        $pct  = $data['total'] > 0 ? round(($cat['total_spent'] / $data['total']) * 100) : 0;
        $barColor = $pct >= 60 ? 'var(--gold)' : ($pct >= 30 ? '#3b7dd8' : 'var(--success)');
      ?>
      <div style="
        background:<?= $ci%2===0?'var(--surface)':'var(--surface-2)' ?>;
        border:1px solid var(--border); border-top:none;
        padding:16px 22px;
        <?= $ci===count($data['categories'])-1?'border-radius:0 0 10px 10px;':'' ?>
      ">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
          <div style="display:flex; align-items:center; gap:12px;">
            <div style="font-size:20px;"><?= $icon ?></div>
            <div>
              <div style="font-size:14px; font-weight:500; color:var(--text);">
                <?= htmlspecialchars($cat['category']) ?>
              </div>
              <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                <?= $cat['total_entries'] ?> expense<?= $cat['total_entries']>1?'s':'' ?>
                &nbsp;·&nbsp;
                Avg ₹<?= number_format($cat['avg_expense'], 0) ?>
                &nbsp;·&nbsp;
                Max ₹<?= number_format($cat['highest_expense'], 0) ?>
              </div>
            </div>
          </div>
          <div style="text-align:right;">
            <div style="font-family:'Cormorant Garamond',serif; font-size:20px; color:var(--gold-light);">
              ₹<?= number_format($cat['total_spent'], 2) ?>
            </div>
            <div style="font-size:11px; color:var(--text-muted);"><?= $pct ?>% of month</div>
          </div>
        </div>
        <!-- Spend bar -->
        <div style="height:5px; background:var(--surface-3); border-radius:100px; overflow:hidden;">
          <div style="width:<?= $pct ?>%; height:100%; background:<?= $barColor ?>; border-radius:100px; transition:width 0.6s;"></div>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
    <?php endforeach; ?>

  <?php endif; ?>

  <div style="margin-top:16px; text-align:center;">
    <a href="view.php" class="btn-gold-outline">← Back to Dashboard</a>
  </div>

</section>

<footer class="footer">
  <div class="footer-brand">Aurex</div>
  <div class="footer-text">Your financial data stays on your machine. Always.</div>
</footer>

<script>
const tCtx = document.getElementById('trendChart');
if (tCtx) {
  new Chart(tCtx, {
    type: 'line',
    data: {
      labels: <?= json_encode($chartLabels) ?>,
      datasets: [{
        label: 'Total Spent (₹)',
        data: <?= json_encode($chartValues) ?>,
        borderColor: '#c9a84c',
        backgroundColor: 'rgba(201,168,76,0.08)',
        borderWidth: 2.5,
        pointBackgroundColor: '#c9a84c',
        pointRadius: 5,
        pointHoverRadius: 7,
        fill: true,
        tension: 0.4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: ctx => ` ₹${ctx.parsed.y.toLocaleString('en-IN')}` } }
      },
      scales: {
        x: { ticks:{color:'#8a8578'}, grid:{color:'rgba(255,255,255,0.04)'} },
        y: {
          ticks:{ color:'#8a8578', callback: v => '₹'+v.toLocaleString('en-IN') },
          grid:{ color:'rgba(255,255,255,0.04)' }
        }
      }
    }
  });
}
</script>

</body>
</html>

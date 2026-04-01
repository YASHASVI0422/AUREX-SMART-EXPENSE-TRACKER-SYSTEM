<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — Aurex</title>
  <link rel="stylesheet" href="style.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "expense_tracker");

// ── All expenses ──
$result = $conn->query("SELECT * FROM expenses ORDER BY date DESC");
$total  = 0;
$rows   = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    $total += $row['amount'];
}

// ── This month's spend ──
$month       = date('Y-m');
$monthRes    = $conn->query("SELECT SUM(amount) as s FROM expenses WHERE DATE_FORMAT(date,'%Y-%m')='$month'");
$monthSpend  = floatval($monthRes->fetch_assoc()['s'] ?? 0);

// ── Biggest category this month ──
$catData = [];
$catRes  = $conn->query("
    SELECT category, SUM(amount) as total
    FROM expenses
    WHERE DATE_FORMAT(date,'%Y-%m') = '$month'
    GROUP BY category ORDER BY total DESC
");
while ($r = $catRes->fetch_assoc()) {
    $catData[$r['category']] = floatval($r['total']);
}
$biggestCat    = !empty($catData) ? array_key_first($catData) : '—';
$biggestCatAmt = !empty($catData) ? reset($catData) : 0;

// ── Most expensive single expense ──
$maxRow = $conn->query("SELECT title, amount FROM expenses ORDER BY amount DESC LIMIT 1")->fetch_assoc();
$maxTitle  = $maxRow['title']  ?? '—';
$maxAmount = floatval($maxRow['amount'] ?? 0);

// ── Days left in month ──
$daysLeft = (int) date('t') - (int) date('j');

// ── Monthly trend ──
$monthlyData   = [];
$monthlyLabels = [];
$mRes = $conn->query("
    SELECT DATE_FORMAT(date,'%b %Y') as mo, SUM(amount) as total
    FROM expenses
    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(date,'%Y-%m')
    ORDER BY MIN(date) ASC
");
while ($r = $mRes->fetch_assoc()) {
    $monthlyLabels[] = $r['mo'];
    $monthlyData[]   = floatval($r['total']);
}

// ── Budget limits ──
$limits = [];
$lRes   = $conn->query("SELECT category, monthly_limit FROM budget_limits");
if ($lRes) { while ($r = $lRes->fetch_assoc()) $limits[$r['category']] = floatval($r['monthly_limit']); }

// ── Overall budget (from settings if exists) ──
$monthlyBudget = 5000;
$sRes = $conn->query("SELECT value FROM settings WHERE key_name='monthly_budget'");
if ($sRes && $sRow = $sRes->fetch_assoc()) $monthlyBudget = floatval($sRow['value']);

$budgetExceeded = $monthSpend > $monthlyBudget;
$budgetPct      = $monthlyBudget > 0 ? min(100, round(($monthSpend / $monthlyBudget) * 100)) : 0;
$categoryIcons  = ['Food'=>'🍔','Travel'=>'✈️','Shopping'=>'🛍️','Education'=>'📚'];
?>

<!-- ── NAVBAR ── -->
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <div class="navbar-logo">💸</div>
    <span class="navbar-title">Aurex</span>
  </a>
  <div class="navbar-links">
    <a href="index.php">Home</a>
    <a href="view.php" class="active">Dashboard</a>
    <a href="budgets.php">Budgets</a>
    <a href="monthly_report.php">Reports</a>
    <a href="history.php">History</a>
  </div>
</nav>

<section class="view-section">

  <!-- Toast -->
  <?php if (isset($_GET['success'])): ?>
    <div class="toast <?= $_GET['success']==='deleted'?'toast-danger':'toast-success' ?>">
      <?= $_GET['success']==='deleted' ? '🗑 Expense deleted.' : '✓ Expense updated successfully.' ?>
    </div>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="dash-header">
    <div>
      <div class="section-title">Financial Overview</div>
      <h1 class="dash-title">Your Expense Dashboard</h1>
      <p class="dash-sub">All your transactions, charts, and budget health in one place.</p>
    </div>
    <a href="index.php" class="btn-gold-outline">+ Add Expense</a>
  </div>

  <!-- ══════════════════════════════════════
       FEATURE 1 — SUMMARY CARDS
  ══════════════════════════════════════ -->
  <div class="summary-cards">

    <!-- Card 1: This month -->
    <div class="summary-card" style="--card-accent:#c9a84c;">
      <div class="sc-icon">📅</div>
      <div class="sc-body">
        <div class="sc-label">This Month</div>
        <div class="sc-value counter" data-target="<?= $monthSpend ?>">₹0</div>
        <div class="sc-sub">
          <?php
            $barColor = $budgetPct >= 90 ? 'var(--danger)' : ($budgetPct >= 65 ? '#e0a352' : 'var(--success)');
          ?>
          <div class="sc-progress-track">
            <div class="sc-progress-fill" style="width:<?= $budgetPct ?>%; background:<?= $barColor ?>;"></div>
          </div>
          <span style="color:<?= $barColor ?>;"><?= $budgetPct ?>%</span> of ₹<?= number_format($monthlyBudget,0) ?> budget
        </div>
      </div>
    </div>

    <!-- Card 2: Biggest category -->
    <div class="summary-card" style="--card-accent:#3b7dd8;">
      <div class="sc-icon"><?= $categoryIcons[$biggestCat] ?? '📊' ?></div>
      <div class="sc-body">
        <div class="sc-label">Top Category</div>
        <div class="sc-value"><?= $biggestCat ?></div>
        <div class="sc-sub">
          <?php if ($biggestCatAmt > 0): ?>
            <span class="counter" data-target="<?= $biggestCatAmt ?>" data-prefix="₹">₹0</span> spent this month
          <?php else: ?>
            No data this month
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Card 3: Most expensive -->
    <div class="summary-card" style="--card-accent:#9b59b6;">
      <div class="sc-icon">💸</div>
      <div class="sc-body">
        <div class="sc-label">Biggest Expense</div>
        <div class="sc-value" style="font-size:18px; line-height:1.3;">
          <?= htmlspecialchars(strlen($maxTitle) > 18 ? substr($maxTitle,0,18).'…' : $maxTitle) ?>
        </div>
        <div class="sc-sub">
          <span class="counter" data-target="<?= $maxAmount ?>" data-prefix="₹">₹0</span> total
        </div>
      </div>
    </div>

    <!-- Card 4: Days left -->
    <div class="summary-card" style="--card-accent:#1c8a5a;">
      <div class="sc-icon">⏳</div>
      <div class="sc-body">
        <div class="sc-label">Days Left</div>
        <div class="sc-value counter" data-target="<?= $daysLeft ?>" data-prefix="" data-suffix=" days"><?= $daysLeft ?> days</div>
        <div class="sc-sub">remaining in <?= date('F') ?></div>
      </div>
    </div>

  </div>

  <!-- Budget alerts -->
  <?php foreach ($limits as $cat => $limit):
    $s = floatval($catData[$cat] ?? 0);
    if ($limit > 0 && $s > $limit): ?>
      <div class="alert-banner">
        ⚠️ <strong><?= ($categoryIcons[$cat]??'').' '.$cat ?></strong>
        — spent ₹<?= number_format($s,0) ?> vs ₹<?= number_format($limit,0) ?> limit.
      </div>
  <?php endif; endforeach; ?>

  <!-- Charts -->
  <?php if (!empty($rows)): ?>
  <div class="charts-row">
    <div class="chart-card">
      <div class="chart-title">
        <div class="section-title">This Month</div>
        <h3>Spending by Category</h3>
      </div>
      <div class="chart-wrap" style="max-width:260px; margin:0 auto;">
        <canvas id="donutChart"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <div class="chart-title">
        <div class="section-title">Trend</div>
        <h3>Monthly Spending</h3>
      </div>
      <div class="chart-wrap">
        <canvas id="barChart"></canvas>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════
       FEATURE 3 — SEARCH & FILTER BAR
  ══════════════════════════════════════ -->
  <?php if (!empty($rows)): ?>
  <div class="filter-bar">
    <div class="filter-search-wrap">
      <span class="filter-search-icon">🔍</span>
      <input
        type="text"
        id="searchInput"
        class="filter-search"
        placeholder="Search expenses by title..."
        oninput="applyFilters()"
      />
    </div>
    <div class="filter-chips" id="filterChips">
      <button class="chip active" data-cat="all" onclick="setCategory(this,'all')">All</button>
      <button class="chip" data-cat="Food" onclick="setCategory(this,'Food')">🍔 Food</button>
      <button class="chip" data-cat="Travel" onclick="setCategory(this,'Travel')">✈️ Travel</button>
      <button class="chip" data-cat="Shopping" onclick="setCategory(this,'Shopping')">🛍️ Shopping</button>
      <button class="chip" data-cat="Education" onclick="setCategory(this,'Education')">📚 Education</button>
    </div>
    <div class="filter-sort-wrap">
      <select id="sortSelect" onchange="applyFilters()" class="filter-sort">
        <option value="date-desc">Newest First</option>
        <option value="date-asc">Oldest First</option>
        <option value="amount-desc">Highest Amount</option>
        <option value="amount-asc">Lowest Amount</option>
      </select>
    </div>
  </div>

  <!-- Results count -->
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
    <div class="section-title" id="resultsCount">
      <?= count($rows) ?> transactions
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
      <a href="export.php" style="
        font-size:12px; color:var(--success); text-decoration:none;
        border:1px solid rgba(76,175,130,0.3); padding:6px 14px;
        border-radius:8px; transition:all 0.2s;
      " onmouseover="this.style.background='rgba(76,175,130,0.08)'"
         onmouseout="this.style.background='transparent'">
        ⬇ Export CSV
      </a>
      <button id="clearFilters" onclick="clearAllFilters()"
        style="display:none; font-size:12px; color:var(--gold); background:none; border:1px solid var(--border); padding:6px 14px; border-radius:8px; cursor:pointer; letter-spacing:0.05em;">
        ✕ Clear filters
      </button>
    </div>
  </div>
  <?php endif; ?>

  <!-- Expense list -->
  <?php if (empty($rows)): ?>
    <div style="text-align:center; padding:80px 20px; color:var(--text-muted);">
      <div style="font-size:48px; margin-bottom:16px;">📭</div>
      <div style="font-family:'Cormorant Garamond',serif; font-size:22px; color:var(--text-dim);">No expenses yet</div>
      <p style="font-size:14px; margin-top:8px;">
        Add your first expense from the <a href="index.php" style="color:var(--gold); text-decoration:none;">home page</a>.
      </p>
    </div>
  <?php else: ?>
    <div class="expenses-list" id="expenseList">
      <?php foreach ($rows as $row):
        $icon          = $categoryIcons[$row['category']] ?? '💳';
        $dateFormatted = date('d M Y', strtotime($row['date']));
      ?>
        <div class="expense-row"
             data-title="<?= strtolower(htmlspecialchars($row['title'])) ?>"
             data-category="<?= htmlspecialchars($row['category']) ?>"
             data-amount="<?= $row['amount'] ?>"
             data-date="<?= $row['date'] ?>">
          <div class="expense-left">
            <div class="expense-category-icon"><?= $icon ?></div>
            <div>
              <div class="expense-title"><?= htmlspecialchars($row['title']) ?></div>
              <div class="expense-meta"><?= htmlspecialchars($row['category']) ?> &nbsp;·&nbsp; <?= $dateFormatted ?></div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:20px;">
            <div class="expense-amount">₹<?= number_format($row['amount'], 2) ?></div>
            <div class="expense-actions">
              <a href="edit.php?id=<?= $row['id'] ?>" class="action-btn edit-btn" title="Edit">✏️</a>
              <a href="delete.php?id=<?= $row['id'] ?>"
                 class="action-btn delete-btn" title="Delete"
                 onclick="return confirm('Delete this expense? This cannot be undone.')">🗑</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- No results state -->
    <div id="noResults" style="display:none; text-align:center; padding:60px 20px;">
      <div style="font-size:40px; margin-bottom:12px;">🔍</div>
      <div style="font-family:'Cormorant Garamond',serif; font-size:20px; color:var(--text-dim);">No matching expenses</div>
      <p style="font-size:14px; color:var(--text-muted); margin-top:6px;">Try a different search or filter.</p>
    </div>
  <?php endif; ?>

  <div style="margin-top:48px; text-align:center;">
    <a href="index.php" class="btn-gold-outline">← Add Another Expense</a>
  </div>

</section>

<footer class="footer">
  <div class="footer-brand">Aurex</div>
  <div class="footer-text">Your financial data stays on your machine. Always.</div>
</footer>

<!-- ════════════════════════════════════════
     SCRIPTS: Charts + Counters + Filter
════════════════════════════════════════ -->
<script>
// ── Charts ──────────────────────────────
const donutCtx = document.getElementById('donutChart');
if (donutCtx) {
  new Chart(donutCtx, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_keys($catData)) ?>,
      datasets: [{
        data: <?= json_encode(array_values($catData)) ?>,
        backgroundColor: ['#c9a84c','#1c8a5a','#3b7dd8','#9b59b6'],
        borderColor: '#0e1219', borderWidth: 3, hoverOffset: 8,
      }]
    },
    options: {
      cutout: '68%',
      plugins: {
        legend: { position:'bottom', labels:{ color:'#8a8578', font:{size:12}, padding:16 } },
        tooltip: { callbacks: { label: ctx => ` ₹${ctx.parsed.toLocaleString('en-IN')}` } }
      }
    }
  });
}

const barCtx = document.getElementById('barChart');
if (barCtx) {
  new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($monthlyLabels) ?>,
      datasets: [{
        label: 'Spent (₹)',
        data: <?= json_encode($monthlyData) ?>,
        backgroundColor: 'rgba(201,168,76,0.22)',
        borderColor: '#c9a84c', borderWidth: 2,
        borderRadius: 6, hoverBackgroundColor: 'rgba(201,168,76,0.45)',
      }]
    },
    options: {
      responsive: true,
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

// ── FEATURE 2: Animated Number Counters ──
function animateCounter(el) {
  const target  = parseFloat(el.dataset.target) || 0;
  const prefix  = el.dataset.prefix !== undefined ? el.dataset.prefix : '₹';
  const suffix  = el.dataset.suffix || '';
  const isFloat = target % 1 !== 0;
  const duration = 1400;
  const start    = performance.now();

  function step(now) {
    const elapsed  = now - start;
    const progress = Math.min(elapsed / duration, 1);
    // Ease out cubic
    const ease     = 1 - Math.pow(1 - progress, 3);
    const current  = target * ease;
    el.textContent = prefix + (isFloat
      ? current.toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2})
      : Math.round(current).toLocaleString('en-IN')
    ) + suffix;
    if (progress < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

// Trigger counters when cards enter viewport
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.querySelectorAll('.counter').forEach(animateCounter);
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.2 });

document.querySelectorAll('.summary-card').forEach(card => observer.observe(card));

// ── FEATURE 3: Search & Filter ──────────
let activeCategory = 'all';

function setCategory(btn, cat) {
  activeCategory = cat;
  document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
  btn.classList.add('active');
  applyFilters();
}

function applyFilters() {
  const query  = document.getElementById('searchInput').value.toLowerCase().trim();
  const sort   = document.getElementById('sortSelect').value;
  const list   = document.getElementById('expenseList');
  const noRes  = document.getElementById('noResults');
  const count  = document.getElementById('resultsCount');
  const clearBtn = document.getElementById('clearFilters');

  if (!list) return;

  const rows = Array.from(list.querySelectorAll('.expense-row'));

  // Filter
  let visible = rows.filter(row => {
    const titleMatch = row.dataset.title.includes(query);
    const catMatch   = activeCategory === 'all' || row.dataset.category === activeCategory;
    return titleMatch && catMatch;
  });

  // Sort
  visible.sort((a, b) => {
    if (sort === 'amount-desc') return parseFloat(b.dataset.amount) - parseFloat(a.dataset.amount);
    if (sort === 'amount-asc')  return parseFloat(a.dataset.amount) - parseFloat(b.dataset.amount);
    if (sort === 'date-asc')    return a.dataset.date.localeCompare(b.dataset.date);
    return b.dataset.date.localeCompare(a.dataset.date); // date-desc default
  });

  // Hide all, re-append sorted visible ones
  rows.forEach(r => { r.style.display = 'none'; list.appendChild(r); });
  visible.forEach(r => r.style.display = 'flex');

  // Update UI
  const n = visible.length;
  count.textContent = n + ' transaction' + (n !== 1 ? 's' : '');
  noRes.style.display  = n === 0 ? 'block' : 'none';
  list.style.display   = n === 0 ? 'none'  : 'flex';

  const filtersActive = query !== '' || activeCategory !== 'all' || sort !== 'date-desc';
  clearBtn.style.display = filtersActive ? 'inline-block' : 'none';
}

function clearAllFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('sortSelect').value  = 'date-desc';
  setCategory(document.querySelector('.chip[data-cat="all"]'), 'all');
}
</script>

</body>
</html>

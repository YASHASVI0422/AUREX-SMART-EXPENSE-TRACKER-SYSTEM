<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Aurex — Smart Expense Tracker</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<!-- ─── NAVBAR ─── -->
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <div class="navbar-logo">💸</div>
    <span class="navbar-title">Aurex</span>
  </a>
  <div class="navbar-links">
    <a href="index.php" class="active">Home</a>
    <a href="view.php">Dashboard</a>
  </div>
</nav>

<!-- ─── HERO ─── -->
<section class="hero">
  <div class="hero-glow"></div>
  <div class="hero-tag">✦ Personal Finance Intelligence</div>
  <h1>
    Master Your Money<br>
    With <em>Precision & Clarity</em>
  </h1>
  <p class="hero-sub">
    Aurex is your personal financial companion — track every rupee, understand your spending patterns, and take control of your financial future with elegance.
  </p>
  <div class="hero-divider"></div>
  <div class="stats-bar">
    <div class="stat-item">
      <span class="stat-number">100%</span>
      <div class="stat-label">Private & Local</div>
    </div>
    <div class="stat-item">
      <span class="stat-number">Real-time</span>
      <div class="stat-label">Budget Alerts</div>
    </div>
    <div class="stat-item">
      <span class="stat-number">4+</span>
      <div class="stat-label">Categories</div>
    </div>
    <div class="stat-item">
      <span class="stat-number">₹5K</span>
      <div class="stat-label">Smart Threshold</div>
    </div>
  </div>
</section>

<!-- ─── FEATURES ─── -->
<section class="features-section">
  <div class="features-header">
    <div class="section-title">What Aurex Offers</div>
    <h2>Designed for Financial Clarity</h2>
  </div>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">📊</div>
      <h3>Expense Tracking</h3>
      <p>Log every transaction instantly. Whether it's a morning chai or a monthly subscription — nothing slips through the cracks.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">⚡</div>
      <h3>Budget Alerts</h3>
      <p>Smart threshold monitoring warns you the moment you cross your spending limit — so you stay in control before it's too late.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🗂️</div>
      <h3>Category Insights</h3>
      <p>Organize expenses by Food, Travel, Shopping, and Education. Understand exactly where your money flows each month.</p>
    </div>
  </div>
</section>

<!-- ─── FORM ─── -->
<section class="form-section">
  <div class="form-header">
    <div class="section-title">Add New Entry</div>
    <h2>Record an Expense</h2>
    <p>Fill in the details below to log a new expense into your tracker.</p>
  </div>

  <div class="form-card">
    <form action="add.php" method="POST">
      <div class="form-row">
        <div class="form-group">
          <label for="title">Expense Title</label>
          <input type="text" id="title" name="title" placeholder="e.g. Dinner at restaurant" required />
        </div>
        <div class="form-group">
          <label for="amount">Amount (₹)</label>
          <input type="number" id="amount" name="amount" placeholder="0.00" required />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="category">Category</label>
          <div class="select-wrapper">
            <select id="category" name="category">
              <option value="Food">🍔 Food</option>
              <option value="Travel">✈️ Travel</option>
              <option value="Shopping">🛍️ Shopping</option>
              <option value="Education">📚 Education</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="date">Date</label>
          <input type="date" id="date" name="date" required />
        </div>
      </div>
      <button type="submit" class="submit-btn">
        <span>➕ &nbsp; Add Expense</span>
      </button>
    </form>
  </div>
</section>

<!-- ─── FOOTER ─── -->
<footer class="footer">
  <div class="footer-brand">Aurex</div>
  <div class="footer-text">Your financial data stays on your machine. Always.</div>
</footer>

</body>
</html>

# 💸 Aurex — Smart Expense Tracker

Aurex is a full-stack **personal finance management web application** that helps users track expenses, manage budgets, and gain insights into their spending patterns.

Built using **PHP, MySQL, HTML, CSS, JavaScript, and Chart.js**, Aurex demonstrates advanced database concepts like **Triggers, Views, UPSERT operations, and Referential Integrity**.

---

## 🚀 Features

- 📝 **Expense Tracking (CRUD)**
  - Add, edit, delete expenses easily
- 📊 **Interactive Dashboard**
  - Real-time analytics, charts, and KPIs
- 💰 **Budget Management**
  - Set category-wise monthly limits
  - Get alerts when exceeding budgets
- 📋 **Monthly Reports**
  - Powered by MySQL VIEW (no heavy PHP logic)
- 🗑 **Deletion History**
  - Automatically logged using database trigger
- 🔍 **Search & Filter**
  - Dynamic filtering without page reload
- 📤 **Export Data**
  - Download expenses as CSV

---

## 🛠️ Tech Stack

| Layer        | Technology Used |
|-------------|----------------|
| Frontend     | HTML5, CSS3, JavaScript |
| Backend      | PHP (MySQLi) |
| Database     | MySQL (phpMyAdmin) |
| Visualization| Chart.js |
| Environment  | XAMPP |

---

## 🗄️ Database Design

The system uses a relational database: **`expense_tracker`**

### Tables:
- `expenses` — stores all transactions
- `categories` — predefined categories
- `budget_limits` — monthly spending limits
- `expense_log` — logs deleted records (trigger-based)
- `settings` — system configurations

---

## ⚡ Advanced Database Concepts Used

### 🔹 Trigger
- `BEFORE DELETE` trigger logs deleted expenses into `expense_log`

### 🔹 View
- `monthly_summary` view generates reports using:
  - `SUM()`, `AVG()`, `COUNT()`, `GROUP BY`

### 🔹 UPSERT
- `ON DUPLICATE KEY UPDATE` used for budget management

### 🔹 Referential Integrity
- Foreign keys maintain valid relationships between tables

---

## 📂 Project Structure

```
Aurex/
│
├── index.php              # Homepage & expense form
├── view.php               # Dashboard
├── add.php                # Add expense
├── edit.php               # Edit expense
├── update.php             # Update logic
├── delete.php             # Delete expense
├── budgets.php            # Budget management
├── monthly_report.php     # Reports (VIEW-based)
├── history.php            # Trigger logs
├── export.php             # CSV export
├── style.css              # UI styling
└── expense_tracker.sql    # Database file
```

---

## ⚙️ Installation & Setup

### 🔧 Prerequisites
- XAMPP / WAMP / LAMP
- PHP 8+
- MySQL

### 📥 Steps

1. Clone the repository:
```bash
git clone https://github.com/your-username/aurex.git
```

2. Move project to:
```
htdocs/
```

3. Import database:
- Open phpMyAdmin
- Create database: `expense_tracker`
- Import `expense_tracker.sql`

4. Start Apache & MySQL

5. Open browser:
```
http://localhost/aurex
```

---

## 📊 Screenshots

> *(Add screenshots here for better presentation)*

- Home Page  
- Dashboard  
- Budget Page  
- Reports  
- History  

---

## 📈 Future Enhancements

- 🔐 User Authentication System
- ☁️ Cloud Deployment
- 📱 Mobile App Version
- 🤖 AI-based Expense Prediction
- 👥 Multi-user Support

---

## 🤝 Contributing

Contributions are welcome! Feel free to fork the repo and submit pull requests.

---

## 📜 License

This project is for educational purposes.

---

## 👨‍💻 Author

**Yashasvi Pandey** , **Mahek Aggarwal**, **Harsh Teotia**
BCA Student | Full Stack Developer  

---

## ⭐ Support

If you like this project, please ⭐ the repository!

# 🏋️ IRONCORE — Enterprise Gym Management & Fitness Ecosystem

<div align="center">

[![Live Demo](https://img.shields.io/badge/🌐_Live_Demo-preyal2.github.io-FF3B30?style=for-the-badge&logo=github&logoColor=white)](https://preyal2.github.io/ironcore-gym-management-system/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?style=for-the-badge&logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![GitHub Pages](https://img.shields.io/badge/GitHub_Pages-Active-22C55E?style=for-the-badge&logo=githubpages&logoColor=white)](https://preyal2.github.io/ironcore-gym-management-system/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

<br>

**A full-stack, role-governed Gym Management & Athlete Performance Tracking Platform designed for modern fitness centers, personal trainers, and gym athletes.**

[🚀 Explore Live Website](https://preyal2.github.io/ironcore-gym-management-system/) • [📖 Technical Documentation](#-system-architecture--directory-structure) • [🎓 Viva Voce Q&A](#-college-viva-voce--technical-defense-cheat-sheet)

</div>

---

## 🌟 Live Cloud Deployment

> 🔗 **Live Website URL**: **[https://preyal2.github.io/ironcore-gym-management-system/](https://preyal2.github.io/ironcore-gym-management-system/)**  
> *Fully interactive online preview featuring zero-error automatic client-side demo engine, animated hero presentation, interactive pricing matrix, and role-governed portals.*

---

## 📖 Executive Summary & Value Proposition

**IRONCORE** is a decoupled 3-tier enterprise fitness management system engineered to automate daily gym workflows, athlete onboarding, personal training assignments, attendance tracking, and financial analytics. 

### Why IRONCORE?
- **Zero-Friction Separation of Concerns**: Strictly separated `frontend/` (Pure HTML5/CSS3/Vanilla JS ES6), `backend/` (PHP 8 REST JSON APIs with PDO), and `database/` (MySQL Relational Schema with 19 Normalized Tables & automated SQLite dev fallback).
- **Dual Runtime Architecture**: Runs locally with full backend PHP/MySQL execution OR as a seamless cloud demo on static platforms like Netlify.
- **Enterprise Dark Aesthetic**: Tailored with athletic dark mode (`#080808` background, `#141414` glassmorphic cards, flame red `#FF3B30` accents, responsive mobile bottom navigation).

---

## 🎯 Role-Based Portals & Feature Matrix

| Feature / Capability | 👑 Admin Portal | 🥊 Trainer Portal | 🏃 Member Portal |
| :--- | :---: | :---: | :---: |
| **Real-time KPI Analytics Dashboard** | ✅ Full Financials | ✅ Assigned Roster | ✅ Athlete Goals |
| **Interactive Chart.js Visualizations** | ✅ Revenue Trends | ❌ | ✅ Weight Progress |
| **Member CRUD & Search Filters** | ✅ All Members | ✅ Assigned Only | ❌ |
| **Staff & Trainer Management** | ✅ Complete | ❌ | ❌ |
| **Membership Plans & Renewals** | ✅ Manage All | ❌ | ✅ Self-Renewal |
| **Offline Invoicing & Printable Receipts** | ✅ Complete | ❌ | ✅ View Receipt |
| **Digital QR / Turnstile Terminal** | ✅ Live Floor Gauge | ✅ Attendance Log | ✅ 1-Click Check-in |
| **Workout Builder & Exercise Library** | ✅ Full Catalog | ✅ Create Routines | ✅ Set Checklist |
| **Diet & Nutrition Macro Calculator** | ❌ | ✅ Assign Macros | ✅ Meal Matrix |
| **Anthropometrics Progress Logs** | ❌ | ✅ Review Logs | ✅ Log Body Stats |
| **1-on-1 Coaching Appointments** | ✅ Monitor | ✅ Approve/Decline | ✅ Book Sessions |
| **CSV Report Data Exports** | ✅ Members & Billing | ❌ | ❌ |
| **Noticeboard & Feedback Moderation** | ✅ Broadcast | ✅ View Notices | ✅ Submit Reviews |

---

## 🔑 Demo Login Credentials

The login page at **[`/login.html`](https://ironcoregym7.netlify.app/login.html)** includes **1-Click Quick Fill Demo Buttons** for instant evaluation:

| Role | Portal URL | Demo Email | Password | Access Scope |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | `/frontend/admin/dashboard.html` | `admin@ironcore.com` | `admin123` | Full financial, membership, trainer, and reporting control |
| **Trainer** | `/frontend/trainer/dashboard.html` | `trainer@ironcore.com` | `trainer123` | Assigned clients, routine builder, diet plans, consultations |
| **Member** | `/frontend/member/dashboard.html` | `member@ironcore.com` | `member123` | Workout logs, meal schedule, digital pass, progress charts |

---

## 📁 System Architecture & Directory Structure

```text
IRONCORE-GYM/
│
├── frontend/                     # Presentation Layer (Client-Side)
│   ├── css/
│   │   ├── style.css             # Core design system tokens, themes, & variables
│   │   ├── landing.css           # Hero section, feature cards, pricing tables
│   │   ├── auth.css              # Glassmorphic login & registration split cards
│   │   ├── dashboard.css         # Collapsible sidebar, stats counters, metric grids
│   │   ├── forms.css             # Input groups, select controls, search filters
│   │   ├── tables.css            # Dark mode data tables & action icons
│   │   ├── cards.css             # Exercise & routine module cards
│   │   ├── modal.css             # Glass modals & printable invoice templates
│   │   └── responsive.css        # Mobile bottom nav bar & responsive breakpoints
│   ├── js/
│   │   ├── api.js                # Centralized Fetch API client with automatic baseUrl & MockDB
│   │   ├── mock-data.js          # Static cloud mock engine for zero-error Netlify demo
│   │   ├── main.js               # Global UI utilities, theme engine, toast notifications
│   │   ├── auth.js               # Session guard, authentication & quick-fill handler
│   │   ├── charts.js             # Chart.js visualization wrappers
│   │   ├── dashboard.js          # Metric aggregation controller
│   │   ├── members.js            # Member lifecycle & search filter controller
│   │   ├── trainers.js           # Trainer management controller
│   │   ├── memberships.js        # Plan subscription & renewal controller
│   │   ├── payments.js           # Invoicing & printable receipt engine
│   │   ├── attendance.js         # Digital check-in & simulated QR terminal
│   │   ├── workouts.js           # Routine & day schedule tracker
│   │   ├── exercises.js          # Movement library & muscle group filter
│   │   ├── diet.js               # Meal planner & macro calculator
│   │   ├── progress.js           # Anthropometric logs & streak calculator
│   │   ├── appointments.js       # 1-on-1 PT booking & approval workflow
│   │   ├── notifications.js      # Alert badge & notification dropdown
│   │   └── reports.js            # Client-side CSV export generator
│   ├── admin/                    # Admin Portal Views (15 HTML Views)
│   ├── trainer/                  # Trainer Portal Views (11 HTML Views)
│   ├── member/                   # Member Portal Views (13 HTML Views)
│   ├── index.html                # Public Landing Page
│   ├── login.html                # Multi-Role Portal Authentication View
│   └── register.html             # Member Self-Onboarding View
│
├── backend/                      # Application Layer (PHP 8 REST APIs)
│   ├── config/
│   │   ├── database.php          # PDO abstraction (MySQL + SQLite dev fallback)
│   │   └── response.php          # JSON serialization, CORS, & session guards
│   ├── auth/                     # login.php, logout.php, register.php, session.php
│   ├── members/                  # list.php, get.php, add.php, update.php, delete.php
│   ├── trainers/                 # list.php, get.php, add.php, update.php, delete.php
│   ├── memberships/              # list.php, get.php, add.php, plans.php, renew.php
│   ├── payments/                 # list.php, get.php, add.php, receipt.php
│   ├── attendance/               # today.php, checkin.php, checkout.php, history.php
│   ├── workouts/                 # list.php, get.php, add.php, update.php, complete.php
│   ├── exercises/                # list.php, get.php, add.php, update.php, delete.php
│   ├── diet/                     # list.php, get.php, add.php, assign.php
│   ├── progress/                 # list.php, summary.php, add.php
│   ├── appointments/             # list.php, create.php, approve.php, reject.php
│   ├── notifications/            # list.php, create.php, read.php
│   ├── announcements/            # list.php, add.php, delete.php
│   ├── feedback/                 # list.php, add.php, delete.php
│   └── reports/                  # revenue.php, attendance.php, members.php, payments.php
│
└── database/                     # Data Layer
    ├── schema.sql                # Normalized 19 tables DDL with foreign keys
    ├── seed.sql                  # Comprehensive sample data (20 members, 5 trainers)
    └── ironcore_gym.sql          # Single-file import script for phpMyAdmin
```

---

## ⚡ Quick Start & Local Installation

### Method 1: Zero-Config Standalone PHP Server (Fastest)

Run the full-stack system locally with zero external installations:

```bash
# 1. Clone the repository
git clone https://github.com/preyal2/ironcore-gym-management-system.git

# 2. Enter project directory
cd ironcore-gym-management-system

# 3. Start the PHP server
php -S 127.0.0.1:8000
```
👉 Open your browser at: **`http://127.0.0.1:8000/frontend/index.html`**  
*(The backend auto-detects database availability and transparently bootstraps SQLite with sample records).*

---

### Method 2: Standard XAMPP Setup (Production Workflow)

1. **Copy to Web Root**:
   Clone or copy the project folder into your XAMPP `htdocs` directory:
   ```text
   C:\xampp\htdocs\ironcore-gym-management-system\
   ```
2. **Start Services**:
   Open XAMPP Control Panel and click **Start** for **Apache** and **MySQL**.
3. **Import Database**:
   - Open **`http://localhost/phpmyadmin`**
   - Create a new database named **`ironcore_gym`**
   - Click **Import** tab -> Select **`database/ironcore_gym.sql`** -> Click **Go**
4. **Launch Application**:
   - Navigate to: **`http://localhost/ironcore-gym-management-system/frontend/index.html`**

---

## 🗄️ Relational Database Model (19 Tables)

The database schema (`database/schema.sql`) adheres to 3NF (Third Normal Form) normalization:
- **Core Entities**: `users`, `trainers`, `members`, `membership_plans`, `memberships`, `payments`, `attendance`
- **Fitness & Routines**: `exercises`, `workout_plans`, `workout_exercises`, `workout_progress`
- **Nutrition & Diet**: `diet_plans`, `diet_meals`, `member_diets`
- **Tracking & Operations**: `progress`, `appointments`, `notifications`, `announcements`, `feedback`

---

## 🎓 College Viva Voce & Technical Defense Cheat Sheet

### Q1: What architectural pattern does IRONCORE implement?
**Answer**: IRONCORE implements a decoupled **3-Tier Client-Server Architecture**:
- **Presentation Tier**: Pure HTML5, modular CSS3 (CSS custom properties, glassmorphic layout), and Vanilla JS ES6 using standard `Fetch API` and `Chart.js`.
- **Application Tier**: Stateless PHP 8+ REST JSON endpoints communicating exclusively via JSON payloads, protected with server-side session cookies.
- **Data Tier**: Relational schema across 19 normalized tables utilizing Foreign Keys with `CASCADE` & `SET NULL` integrity constraints.

### Q2: How does the system handle database portability between MySQL and SQLite?
**Answer**: The database connection wrapper in [`backend/config/database.php`](backend/config/database.php) uses PHP Data Objects (PDO). It attempts a MySQL socket connection on port 3306. If unavailable, it transparently falls back to an SQLite PDO driver and executes the SQL schema with automated dialect translation (`ENUM` -> `TEXT`, `AUTO_INCREMENT` -> `AUTOINCREMENT`, `DATEDIFF` -> `julianday`), ensuring zero configuration for evaluators.

### Q3: How is authentication and Role-Based Access Control (RBAC) enforced?
**Answer**:
- Passwords are encrypted using PHP's native `password_hash()` implementing the **BCrypt** algorithm (`PASSWORD_BCRYPT`).
- On authenticated login, a server session assigns role tokens (`admin`, `trainer`, `member`).
- Each API invokes `require_auth(['admin'])` or `require_auth(['trainer'])` before executing queries to prevent privilege escalation.

### Q4: How are financial receipts and analytical exports generated?
**Answer**:
- Payment receipts utilize `@media print` CSS style rules that isolate the receipt voucher DOM and strip all navigation headers for printing.
- Reports generate dynamic CSV streams in JavaScript using `new Blob([csv], { type: 'text/csv' })` and invoke client-side download triggers without requiring third-party PDF/Excel server dependencies.

---

## 🛡️ License

This project is open source and available under the **[MIT License](LICENSE)**.

---

<div align="center">

**Developed with ❤️ by [Preyal Modi](https://github.com/preyal2)**  
*For college project presentations, vivas, or commercial inquiries, visit the [GitHub Repository](https://github.com/preyal2/ironcore-gym-management-system).*

</div>

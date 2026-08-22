# 🏋️ IRONCORE — Enterprise Gym Management & Fitness Ecosystem

<div align="center">

[![Live Demo](https://img.shields.io/badge/🌐_Live_Demo-ironcoregym7.netlify.app-FF3B30?style=for-the-badge)](https://ironcoregym7.netlify.app/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Netlify](https://img.shields.io/badge/Netlify-Deployed-00C7B7?style=for-the-badge&logo=netlify&logoColor=white)](https://ironcoregym7.netlify.app/)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

<br>

**A full-stack, role-governed Gym Management & Athlete Performance Tracking Platform designed for modern fitness centers, personal trainers, and members.**

[Explore Live Demo 🚀](https://ironcoregym7.netlify.app/) • [Report Bug 🐛](https://github.com/preyal2/ironcore-gym-management-system/issues) • [Request Feature 💡](https://github.com/preyal2/ironcore-gym-management-system/issues)

</div>

---

## 🌟 Live Preview

> 🔗 **Live Website URL**: **[https://ironcoregym7.netlify.app/](https://ironcoregym7.netlify.app/)**  
> *Check out the fully animated landing page, pricing calculator, and role-based portal navigation online.*

---

## 📖 Project Overview

**IRONCORE** is a decoupled 3-tier enterprise fitness management system built to automate daily gym operations, member onboarding, personal training assignments, attendance tracking, and financial analytics. 

Engineered with clean architectural boundaries between **Presentation (HTML5/CSS3/Vanilla JS)**, **Business Logic (PHP 8 REST APIs with PDO)**, and **Data Storage (MySQL relational schema with automatic SQLite dev fallback)**.

---

## 🎯 Key Capabilities & Role-Based Portals

### 👑 1. Executive Admin Portal (15 Pages)
- **Real-Time Analytics Dashboard**: Live KPI counters, monthly revenue growth curve (Chart.js), dynamic gym capacity gauge, and attendance trends.
- **Member Management**: Comprehensive CRUD operations, search & multi-parameter filter (by plan, gender, goal, status), member profile inspector.
- **Trainer & Staff Directory**: Trainer onboarding, client allocation matrix, certification records.
- **Membership & Billing Engine**: Plan lifecycle management, auto-renewals, offline payment logging, and instant printable tax receipts.
- **Digital QR & Turnstile Terminal**: Simulated check-in terminal with instant entry/exit logging and live floor capacity counter.
- **Reporting & Auditing**: 1-click CSV report exports for member rosters, revenue breakdowns, and attendance logs.
- **Broadcast Announcements & Review Moderation**: Global noticeboard with priority tags and member feedback aggregator.

### 🥊 2. Personal Trainer Portal (11 Pages)
- **Assigned Client Roster**: Direct access to assigned athletes, fitness goals, and intake metrics.
- **Interactive Workout Builder**: Day-by-day training routine creator with exercise catalog linking, set/rep/rest configurations.
- **Diet & Nutrition Planner**: Caloric target matrix and macronutrient distributions (Proteins, Carbs, Fats) across daily meals.
- **Client Progress Monitor**: Anthropometric logs (Weight, Waist, Chest, Arms, Legs) with milestone tracking.
- **1-on-1 Appointment Management**: Session request approval, decline, and scheduling workflow.

### 🏃 3. Athlete & Member Portal (13 Pages)
- **Personal Athlete Hub**: Active membership countdown banner, daily workout plan preview, and quick gym check-in.
- **Digital Membership Pass**: Barcode pass generator, expiry tracker, and self-service plan renewal dialog.
- **Dynamic Workout Tracker**: Step-by-step day schedule with interactive checkbox completion state for every set.
- **Nutrition Matrix**: Meal timings, food item lists, and macro breakdown per meal.
- **Body Recomposition Curves**: Weight and circumference visual analytics powered by Chart.js.
- **Coach Consultations**: 1-click coaching session booking for form checks and nutrition reviews.

---

## 🔑 Demo Login Credentials

The application features **1-Click Quick Fill Demo Buttons** on the login page for instant role evaluation:

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
│   │   ├── api.js                # Centralized Fetch API client with automatic baseUrl
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

No Apache or XAMPP installation required! Run directly using the built-in PHP development server:

```bash
# 1. Clone the repository
git clone https://github.com/preyal2/ironcore-gym-management-system.git

# 2. Enter project directory
cd ironcore-gym-management-system

# 3. Start the PHP server
php -S 127.0.0.1:8000
```
👉 Open your browser at: **`http://127.0.0.1:8000/frontend/index.html`**  
*(The backend automatically creates and seeds a local SQLite database if MySQL is not running).*

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

This project is licensed under the **MIT License** — feel free to use, modify, and distribute for academic and commercial purposes.

---

<div align="center">

**Developed with ❤️ by [Preyal Modi](https://github.com/preyal2)**  
*For questions, presentations, or feedback, open an issue on GitHub.*

</div>

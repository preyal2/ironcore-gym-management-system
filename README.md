# 🏋️ IRONCORE — Premium Full-Stack Gym Management System

**IRONCORE** is a full-stack, enterprise-grade Gym Management & Fitness Platform designed for modern fitness centers, personal trainers, and athletes. Built with a clean separation of concerns, beginner-friendly codebase, and a dark aesthetic UI.

---

## 📁 Architecture & File Structure

```text
IRONCORE-GYM/
│
├── frontend/                     # Client Presentation Layer (HTML5, CSS3, Vanilla JS)
│   ├── css/
│   │   ├── style.css             # Core design system & CSS variables
│   │   ├── landing.css           # Public landing page styles
│   │   ├── auth.css              # Login & registration split card
│   │   ├── dashboard.css         # Sidebar, top header, KPI statistics
│   │   ├── forms.css             # Inputs, selects, filters
│   │   ├── tables.css            # Dark data tables & action icons
│   │   ├── cards.css             # Exercise & routine module cards
│   │   ├── modal.css             # Dialogs & printable receipts
│   │   └── responsive.css        # Mobile bottom navigation & media queries
│   ├── js/
│   │   ├── api.js                # Unified Fetch client
│   │   ├── main.js               # Global UI, toasts, theme toggles
│   │   ├── auth.js               # Session guard & quick demo fill
│   │   ├── charts.js             # Chart.js visualization engine
│   │   ├── dashboard.js          # KPI stats loader
│   │   ├── members.js            # Members CRUD & filter
│   │   ├── trainers.js           # Trainers management
│   │   ├── memberships.js        # Plans & renewals
│   │   ├── payments.js           # Billing & receipt print
│   │   ├── attendance.js         # QR terminal & check-ins
│   │   ├── workouts.js           # Routine & day schedule
│   │   ├── exercises.js          # Movement library
│   │   ├── diet.js               # Daily meal templates
│   │   ├── progress.js           # Weight & anthropometrics
│   │   ├── appointments.js       # 1-on-1 PT bookings
│   │   ├── notifications.js      # Alerts & reminders
│   │   └── reports.js            # CSV exports & print
│   ├── admin/                    # Admin Portal Pages (15 pages)
│   ├── trainer/                  # Trainer Portal Pages (11 pages)
│   ├── member/                   # Member Portal Pages (13 pages)
│   ├── index.html                # Public Landing Page
│   ├── login.html                # Role Portal Login Page
│   └── register.html             # Member Registration Page
│
├── backend/                      # Server Layer (PHP 8+ REST JSON APIs)
│   ├── config/
│   │   ├── database.php          # Dual MySQL / SQLite PDO abstraction
│   │   └── response.php          # JSON handler & session guards
│   ├── auth/                     # Login, register, logout, session
│   ├── members/                  # Members CRUD endpoints
│   ├── trainers/                 # Trainers CRUD endpoints
│   ├── memberships/              # Plan management & renewal APIs
│   ├── payments/                 # Invoicing & printable receipt APIs
│   ├── attendance/               # 1-click & QR check-in/out APIs
│   ├── workouts/                 # Routines & set completions
│   ├── exercises/                # Exercise repository APIs
│   ├── diet/                     # Nutrition & meal plan APIs
│   ├── progress/                 # Body measurement logs
│   ├── appointments/             # Booking & approval endpoints
│   ├── notifications/            # Alerts & read status APIs
│   ├── announcements/            # Broadcast news APIs
│   ├── feedback/                 # Rating & review APIs
│   └── reports/                  # Aggregation & CSV data endpoints
│
└── database/                     # Relational Database Layer
    ├── schema.sql                # 19 normalized tables DDL
    ├── seed.sql                  # Comprehensive realistic seed data
    └── ironcore_gym.sql          # All-in-one import file for phpMyAdmin
```

---

## 🔑 Demo Account Credentials

| Role | Email Address | Password | Description |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@ironcore.com` | `admin123` | Full access to gym finances, members, staff, and reports |
| **Trainer** | `trainer@ironcore.com` | `trainer123` | Assigned clients, workout programming, consultations |
| **Member** | `member@ironcore.com` | `member123` | Personal routine, nutrition chart, attendance, progress |

> 💡 *The login page includes 1-click Quick Demo Fill buttons for immediate evaluation.*

---

## 🚀 Quick Start Guide

### Option A: Running with XAMPP (Standard Local Setup)

1. **Move files into XAMPP**:
   Copy the `IRONCORE-GYM` folder into your XAMPP web root:
   ```text
   C:\xampp\htdocs\IRONCORE-GYM\
   ```
2. **Start Apache & MySQL**:
   Open XAMPP Control Panel and click **Start** for Apache and MySQL.
3. **Import Database**:
   - Open browser: `http://localhost/phpmyadmin`
   - Create a database named `ironcore_gym`.
   - Click **Import** and upload `database/ironcore_gym.sql`.
4. **Launch Application**:
   Open `http://localhost/IRONCORE-GYM/frontend/index.html` in your browser.

---

### Option B: Running via Standalone PHP Built-in Server

If you don't have XAMPP installed, you can run IRONCORE with PHP directly:
```bash
# Navigate to the project root
cd C:\Users\deepm\.gemini\antigravity-ide\scratch\IRONCORE-GYM

# Start the PHP built-in web server
php -S 127.0.0.1:8000
```
Open `http://127.0.0.1:8000/frontend/index.html` in your web browser.  
*(Database auto-detects MySQL; if MySQL is offline, it transparently initializes a local SQLite database with full sample data).*

---

## 🌟 Key Features Summary

1. **Role-Based Access Control (RBAC)**: Distinct views for Admin, Trainer, and Member with session protection.
2. **Interactive QR Terminal & Attendance**: Real-time floor capacity, 1-click check-ins, and streak calculation.
3. **Digital Membership Cards**: Instant barcode generation, countdown badges, and self-service renewals.
4. **Workout Routine Scheduler**: Day-by-day training splits with live set-completion check-offs.
5. **Nutrition & Macro Matrix**: Meal timings, calorie targets, and protein/carb/fat breakdowns.
6. **Chart.js Visualizations**: Revenue growth, attendance trends, plan popularity, and body recomposition curves.
7. **Printable Invoices & Receipts**: Formatted gym fee slips ready for instant browser printing.
8. **CSV Reports Export**: 1-click member and financial exports for auditing.
9. **Dark Premium Gym Theme**: Sleek UI with red flame accents, floating cards, and mobile bottom navigation.

---

## 🎓 College Viva Voce Q&A Cheat Sheet

### Q1: What is the architecture of the IRONCORE project?
**Answer**: IRONCORE utilizes a **3-tier decoupled architecture**:
- **Presentation Layer (Frontend)**: Pure HTML5, Vanilla CSS3 (Custom Design System with CSS variables), and Vanilla JavaScript utilizing the `Fetch API`.
- **Application Layer (Backend)**: PHP 8+ structured as modular REST JSON APIs with PDO database abstraction and secure session cookies.
- **Data Layer (Database)**: MySQL relational database with 19 normalized tables enforcing foreign key constraints and indexed lookups.

### Q2: Why did you separate Frontend and Backend into different directories?
**Answer**: Separating `frontend/` and `backend/` follows the **Single Responsibility Principle** and modern API-driven development. The backend exclusively outputs JSON responses, making it reusable for web interfaces, mobile apps, or third-party integrations without rewriting business logic.

### Q3: How is user authentication and role authorization handled?
**Answer**:
- Passwords are encrypted using PHP's native `password_hash()` with the strong **BCrypt** algorithm (`PASSWORD_DEFAULT`).
- On login, a secure server-side session stores the user's ID and role (`admin`, `trainer`, `member`).
- Each backend API invokes `require_auth(['admin'])` or `require_role()` to verify that the requesting session has the required privilege before executing queries.

### Q4: How does the system handle database portability between MySQL and local testing?
**Answer**: The database connection wrapper in `backend/config/database.php` tests for an active MySQL connection on port 3306. If MySQL is available, it uses MySQL; if running in a standalone development environment without XAMPP, it seamlessly falls back to an SQLite PDO database initialized from `ironcore_gym.sql`.

### Q5: How are financial receipts and report exports implemented?
**Answer**:
- Payment receipts use CSS `@media print` rules that isolate the printable receipt component while hiding navigation elements.
- Reports generate dynamic in-memory CSV blobs using `new Blob([csv], { type: 'text/csv' })` and trigger client-side file downloads without requiring third-party PDF or Excel libraries.

---

**Developed with ❤️ for IronCore Fitness Systems**

# 🚀 FutureMe — Plan Today, Achieve Tomorrow

FutureMe is a personal **goal-planning and progress-tracking web application** designed to help users turn their goals into clear, manageable steps.

Users can create an account, set a goal with a target date, choose an **AI Planner or Manual Planner**, track progress, and create **Future Capsules** containing messages for their future self.

---

## ✨ Core Features

### 🔐 User Registration & Login
- Create an account with name, email, username, and password.
- Password hashing using PHP `password_hash()`.
- Login using username or email.

### 🎯 Goal Creation
- Create a personal goal.
- Add description and category.
- Set start date and target date.
- Store goal information in MySQL.

### 🤖 AI Planner
- Provides goal-based planning suggestions.
- Converts a goal into smaller actionable steps.
- Saves the plan and steps in MySQL.

### ✍️ Manual Planner
- Create a personal plan manually.
- Add multiple steps.
- Store steps in the database.

### 📊 Progress Tracking
- Track goal progress using a percentage slider.
- Save progress in MySQL.
- Update plan-step completion.
- Display progress on the dashboard.

### 📋 My Plans
- View saved AI and Manual plans.
- View all steps belonging to each plan.
- See completed and pending steps.

### 💌 Future Capsule
- Write a message to your future self.
- Add a title and unlock date.
- Store capsule information in MySQL.

### 👤 Profile
- View account information.
- Edit full name.
- View username and email.
- Logout.

### 📱 Responsive Design
- Blue-purple FutureMe theme.
- Clean forms, cards, buttons, and dashboard.
- Responsive layout for different screen sizes.

---

## 🛠️ Tech Stack

### Frontend
- **HTML5** — Page structure
- **CSS3** — Styling and responsive design
- **JavaScript** — Interactivity, validation, Fetch API and localStorage

### Backend
- **PHP** — Backend APIs and server-side processing
- **MySQL** — Database
- **XAMPP** — Local Apache and MySQL environment
- **phpMyAdmin** — Database management

---

## 🗄️ Database

Database name:

```text
futureme
```

### Database Tables

```text
futureme
│
├── users
├── goals
├── plans
├── plan_steps
├── progress
└── future_capsules
```

| Table | Purpose |
|---|---|
| `users` | Stores registered users |
| `goals` | Stores user goals |
| `plans` | Stores AI and Manual plans |
| `plan_steps` | Stores individual plan steps |
| `progress` | Stores goal progress |
| `future_capsules` | Stores future messages and unlock dates |

---

# 🔄 Application Flow

```text
Register
   ↓
Login
   ↓
Dashboard
   ↓
Create Goal
   ↓
Choose Planner
   ├── AI Planner
   └── Manual Planner
          ↓
       Save Plan
          ↓
    Track Progress
          ↓
       Dashboard
          ↓
      My Plans
          ↓
    Future Capsule
```

---

# 🚀 Step-by-Step Localhost Setup Guide

Follow these instructions to run FutureMe on your local machine.

## Prerequisites

Make sure you have:

1. **XAMPP**
2. **Apache**
3. **MySQL**
4. **Web browser**
5. **FutureMe project folder**
6. **FutureMe SQL backup file**

---

## Step 1: Copy the Project

Copy the complete project folder into:

```text
C:\xampp\htdocs\
```

The final structure should look like:

```text
C:\xampp\htdocs\
└── Future_Me\
    ├── api\
    ├── FutureMe_logo.png
    ├── index.html
    ├── style.css
    ├── register.html
    ├── login.html
    ├── dashboard.html
    ├── goal.html
    ├── planner.html
    ├── planner-choice.html
    ├── ai-planner.html
    ├── manual-planner.html
    ├── progress.html
    ├── my-plans.html
    ├── capsule.html
    └── profile.html
```

---

## Step 2: Start XAMPP

Open **XAMPP Control Panel**.

Start:

```text
Apache
MySQL
```

Both services should show as running.

---

## Step 3: Import the Database

Open:

```text
http://localhost/phpmyadmin/
```

Create/select the database:

```text
futureme
```

Use **Import** and select your SQL backup file.

After importing, you should see:

```text
future_capsules
goals
plans
plan_steps
progress
users
```

---

## Step 4: Check Database Connection

The database configuration is in:

```text
api/db.php
```

Default local configuration:

```text
Host: localhost
Username: root
Password: empty
Database: futureme
```

---

## Step 5: Open FutureMe

Open your browser and visit:

```text
http://localhost/Future_Me/
```

---

# 📁 Project Structure

```text
Future_Me/
│
├── api/
│   ├── db.php
│   ├── register.php
│   ├── login.php
│   ├── save_goal.php
│   ├── save_plan.php
│   ├── update_progress.php
│   ├── save_capsule.php
│   └── get_user_data.php
│
├── FutureMe_logo.png
├── index.html
├── style.css
├── register.html
├── login.html
├── dashboard.html
├── goal.html
├── planner.html
├── planner-choice.html
├── ai-planner.html
├── manual-planner.html
├── progress.html
├── my-plans.html
├── capsule.html
└── profile.html
```

---

# 🔗 Frontend → Backend Communication

FutureMe uses JavaScript's **Fetch API** to communicate with PHP backend files.

```text
HTML / JavaScript
       ↓
     Fetch API
       ↓
    PHP API
       ↓
     MySQL
       ↓
   JSON Response
       ↓
HTML / JavaScript
```

Important backend APIs:

```text
register.php
login.php
save_goal.php
save_plan.php
update_progress.php
save_capsule.php
get_user_data.php
```

---

# 🔒 Security

FutureMe includes basic security practices:

- Password hashing using PHP `password_hash()`
- Password verification using `password_verify()`
- Prepared SQL statements
- Backend input validation
- User/goal ownership checks
- Foreign key relationships in MySQL

> For production deployment, stronger session-based authentication, HTTPS, CSRF protection, stricter database constraints, and server-side authorization should be added.

---

# 📸 Application Screenshots

Create a folder named:

```text
screenshots/
```

Then add screenshots such as:

```text
screenshots/
├── home.png
├── register.png
├── login.png
├── dashboard.png
├── goal.png
├── ai-planner.png
├── manual-planner.png
├── progress.png
├── my-plans.png
├── capsule.png
└── profile.png
```

Example README section:

```markdown
## 1. 🏠 Home Page

![Home Page](screenshots/home.png)

## 2. 🔐 Login Page

![Login Page](screenshots/login.png)

## 3. 📊 Dashboard

![Dashboard](screenshots/dashboard.png)
```

---

# 🎓 Project Objective

The main objective of FutureMe is to help users:

```text
Dream
  ↓
Set a Goal
  ↓
Create a Plan
  ↓
Take Action
  ↓
Track Progress
  ↓
Achieve the Goal
```

FutureMe makes personal goal management simple, organized, and motivating.

---

# 🔮 Future Improvements

Possible future improvements include:

- Real AI-powered goal planning using an AI API
- Email reminders
- Goal deadline notifications
- Progress charts and analytics
- Multiple Future Capsules
- Image uploads inside Future Capsules
- Password reset
- Stronger session-based authentication
- Profile image
- Cloud/online deployment
- Mobile application version

---

# 👨‍💻 Developer

**FutureMe — Plan Today, Achieve Tomorrow**

Built using:

```text
HTML + CSS + JavaScript
        +
PHP + MySQL
        +
XAMPP
```

---

## ⭐ Project Highlight

> **Set your goal. Plan your journey. Track your progress. Meet your future self.**

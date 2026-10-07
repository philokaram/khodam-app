# 🎯 Khodam App

A complete system for tracking servants' attendance in choirs and activities.

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8+-4479A1?style=flat&logo=mysql&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=flat&logo=pwa&logoColor=white)
![Built with AI](https://img.shields.io/badge/Built%20with-Claude%20%2B%20DeepSeek-blueviolet)
---

## ✨ Features

### 🎯 Complete Management
- **Servants** — Add, edit, delete, import, export
- **Choirs** — Create and manage
- **Activities** — Mass, Tasbeha, Visitation, and more (fully dynamic)
- **Users** — Full user and role management

### ✅ Attendance Tracking
- Fast registration (Present / Absent / Excused)
- "Mark All Present" in one click
- AJAX-based (no page reload)
- Safe default (Absent)

### 📊 Reports & Statistics
- Real-time Dashboard
- Detailed reports per servant and activity
- Excel export (CSV UTF-8)
- Excel import for servants

### 🔐 Security
- Secure login (bcrypt)
- RBAC — 5 roles with granular permissions
- CSRF Protection
- Session Security
- Audit Logs for every action

### 📱 Progressive Web App
- Installable on mobile
- Works offline (for UI)
- Service Worker + Manifest

### 🌙 Modern UI
- Arabic RTL design
- Dark Mode
- Responsive (mobile-first)
- Smooth animations

---

## 🤖 Built with AI

This entire project was **co-developed with AI assistants** — from architecture to the last CSS rule.

### 🧠 AI Collaborators

| AI | Role |
|----|------|
| **Claude** (Anthropic) | 🏗️ **Primary architect & developer** — built the core architecture, all backend logic, frontend components, database design, security layer, and UI/UX |
| **DeepSeek** | 🔧 **Problem-solver & debugger** — helped resolve hosting-specific issues (ByetHost compatibility, session quirks, CSV import edge cases) and provided alternative solutions when roadblocks appeared |

### 💡 Why AI-Assisted Development?

- ⚡ **Faster iteration** — prototypes in hours, not weeks
- 🎯 **Fewer bugs** — AI caught issues early
- 📚 **Learning** — every decision documented
- 🚀 **Focus on features** — less time on boilerplate
- 🔍 **Cross-verification** — two AIs = better solutions

### ⚠️ Human Oversight

While AI wrote the code, the **human developer**:
- Defined every requirement
- Made architectural decisions
- Tested every feature
- Reviewed security
- Tuned UX for real ministry needs
- Managed deployment

**AI is a tool. The vision was human.**

---

## 🚀 Quick Start

### Requirements
- PHP 8.1+
- MySQL 8+ / MariaDB
- Apache (with mod_rewrite)

### Installation

```bash
# 1. Clone the project
git clone https://github.com/YOUR_USERNAME/khodam-attendance.git
cd khodam-attendance

# 2. Copy the config file
cp .env.example .env

# 3. Edit .env with your database credentials
nano .env

# 4. Import the database
mysql -u root -p -e "CREATE DATABASE choir_attendance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p choir_attendance < database/schema.sql
mysql -u root -p choir_attendance < database/seeds/initial.sql

# 5. Create the admin password hash
php -r "echo password_hash('admin123', PASSWORD_BCRYPT), PHP_EOL;"
# Then update users.password_hash in the database

# 6. Ensure write permissions
chmod -R 755 storage/
```

### Run Locally (Development)

```bash
php -S localhost:8000 -t .
```

Then open: http://localhost:8000

**Username:** `admin`
**Password:** `admin123` (change immediately)

---

## 📁 Project Structure

```
khodam-attendance/
├── app/
│   ├── config/          # Configuration files
│   ├── controllers/     # Controllers
│   ├── models/          # Models
│   ├── services/        # Business logic
│   ├── helpers/         # Helper functions
│   └── views/           # Views
├── assets/
│   ├── css/            # Stylesheets
│   └── js/             # JavaScript
├── routes/              # Routes
├── resources/lang/      # Translations
├── storage/             # Logs, uploads, cache
├── database/            # SQL Schema + Seeds
├── icons/               # PWA icons
├── index.php            # Entry point
├── manifest.json        # PWA Manifest
├── sw.js                # Service Worker
└── .env.example         # Config template
```

---

## 🔐 Roles & Permissions

| Role | Permissions |
|------|-------------|
| **SUPER_ADMIN** | Full access to everything |
| **ADMIN** | Everything except managing SUPER_ADMIN |
| **CHOIR_ADMIN** | Read-only access to own choir |
| **ATTENDANCE_USER** | Register attendance for all choirs |
| **SERVANT** | Personal profile and own attendance history |

### Permission Matrix

| Permission | SUPER_ADMIN | ADMIN | CHOIR_ADMIN | ATTENDANCE_USER | SERVANT |
|------------|:-----------:|:-----:|:-----------:|:---------------:|:-------:|
| View all servants | ✅ | ✅ | ⚠️ Own choir | ✅ | ❌ |
| Create/Edit servant | ✅ | ✅ | ❌ | ❌ | ❌ |
| Delete servant | ✅ | ✅ | ❌ | ❌ | ❌ |
| Manage choirs | ✅ | ✅ | ❌ | ❌ | ❌ |
| Manage activities | ✅ | ✅ | ❌ | ❌ | ❌ |
| Register attendance | ✅ | ✅ | ❌ | ✅ | ❌ |
| View reports | ✅ | ✅ | ⚠️ Own choir | ✅ | ❌ |
| Manage users | ✅ | ✅ | ❌ | ❌ | ❌ |
| View own profile | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 🌍 Hosting Compatibility

Tested on **ByetHost** (free hosting) — works without Composer or Node.js.

**Compatible with:**
- ✅ ByetHost / InfinityFree
- ✅ Any PHP + MySQL host
- ✅ Local server

### Migration Between Hosts

1. Transfer files
2. Import database
3. Update `.env`
4. Done

---

## 📊 Tech Stack

- **Backend:** PHP 8.1+ (No framework)
- **Database:** MySQL 8+ / MariaDB
- **Frontend:** Vanilla JS + CSS3
- **Architecture:** MVC-like
- **PWA:** Service Worker + Manifest

---

## 🧪 Testing

```bash
# Check PHP syntax
find . -name "*.php" -exec php -l {} \;

# Health check endpoint (after installation)
curl http://localhost:8000/api/health
```

**Expected response:**
```json
{
  "success": true,
  "message": "System is working",
  "data": {
    "php": true,
    "pdo": true,
    "db": true
  }
}
```

---

## 🔧 Configuration

### Environment Variables (`.env`)

```env
APP_ENV=production
APP_NAME="Choir Attendance"
APP_URL=https://your-domain.com
APP_TIMEZONE=Africa/Cairo

DB_HOST=localhost
DB_NAME=choir_attendance
DB_USER=root
DB_PASSWORD=
DB_CHARSET=utf8mb4

SESSION_LIFETIME=120
CSRF_TOKEN_NAME=_csrf_token
```

---

## 📡 API Reference

All endpoints return JSON:

```json
{
  "success": true,
  "message": "Operation message",
  "data": {},
  "errors": []
}
```

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login` | Login with username/password |
| POST | `/api/auth/logout` | Logout current user |
| GET | `/api/me` | Get current user info |
| POST | `/api/profile/change-password` | Change own password |

### Servants

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/servants` | List all servants |
| POST | `/api/servants/create` | Create new servant |
| POST | `/api/servants/update` | Update servant |
| POST | `/api/servants/delete` | Delete/deactivate servant |
| POST | `/api/servants/import/parse` | Parse import file |
| POST | `/api/servants/import/confirm` | Confirm import |

### Choires

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/choirs` | List all choirs |
| POST | `/api/choirs/create` | Create new choir |
| POST | `/api/choirs/update` | Update choir |
| POST | `/api/choirs/delete` | Delete choir |

### Activities

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/activities` | List all activities |
| POST | `/api/activities/create` | Create new activity |
| POST | `/api/activities/update` | Update activity |
| POST | `/api/activities/delete` | Delete activity |

### Attendance

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/attendance/session` | Get session data |
| POST | `/api/attendance/save` | Save attendance records |
| GET | `/api/attendance/history` | Get attendance history |

### Statistics

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/statistics/overall` | Overall statistics |
| GET | `/api/statistics/servant` | Per-servant statistics |
| GET | `/api/statistics/choir` | Per-choir statistics |
| GET | `/api/dashboard` | Dashboard data |

### Users

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/users` | List all users |
| POST | `/api/users/create` | Create user |
| POST | `/api/users/update` | Update user |
| POST | `/api/users/delete` | Delete/deactivate user |
| POST | `/api/users/change-password` | Change user's password |
| POST | `/api/users/activate` | Activate user |
| POST | `/api/users/onboarding-done` | Mark onboarding seen |

### Health

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/health` | System health check |


---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

**Please ensure:**
- Code follows existing style
- All tests pass
- No sensitive data is committed

---
## 📊 Project Stats

| Metric | Count |
|--------|-------|
| Controllers | 15+ |
| Models | 9 |
| Services | 6 |
| Views | 35+ |
| Database Tables | 11 |
| API Endpoints | 30+ |
| Permissions | 24 |
| Roles | 5 |
| Languages | Arabic (RTL) |

---
Co-built by 🤖 Claude (Anthropic) + 🧠 DeepSeek + 👨‍💻 Human vision

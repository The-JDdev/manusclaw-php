# ManusClaw PHP

> 🐾 Web interface for ManusClaw AI Agent Framework — Apple-inspired design with full User & Admin panels

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/Database-SQLite-003B57?logo=sqlite&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

## Features

### 🎨 Apple-Style Design
- Glassmorphism cards with backdrop blur
- Dark/Light mode with auto-detection
- Smooth animations and transitions
- Fully responsive (mobile, tablet, desktop)
- Clean, minimal aesthetic with subtle shadows

### 👤 User Panel
- **Dashboard** — Task stats, recent activity, quick actions
- **AI Providers** — Add, edit, test, delete LLM providers (8 types supported)
- **New Task** — Create AI tasks with provider selection and advanced options
- **Task History** — View, filter, retry, cancel tasks
- **Profile** — Update email, change password

### 🔧 Admin Panel
- **Admin Dashboard** — System-wide stats, recent activity, task distribution
- **User Management** — Create, edit, deactivate, delete users
- **System Settings** — General, security, API, email, storage configurations
- **All Tasks** — View and manage tasks across all users
- **All Providers** — Monitor LLM provider configurations
- **Activity Logs** — Track all system actions with auto-refresh
- **System Info** — PHP info, database size, disk space, extensions

### 🤖 Supported LLM Providers
| Provider | Type | API Key Required |
|----------|------|------------------|
| OpenAI | Cloud | ✅ |
| Anthropic | Cloud | ✅ |
| Google AI (Gemini) | Cloud | ✅ |
| HuggingFace | Cloud/Space | ✅ |
| Ollama | Local | ❌ |
| LM Studio | Local | ❌ |
| OpenRouter | Cloud | ✅ |
| Universal | Any OpenAI-compatible | Depends |

## Quick Start

### Requirements
- PHP 8.1 or higher
- PHP extensions: PDO, PDO_SQLite, cURL, mbstring, session
- Apache/Nginx with mod_rewrite (or PHP built-in server for dev)

### Installation

```bash
# Clone the repository
git clone https://github.com/The-JDdev/manusclaw-php.git
cd manusclaw-php

# Set permissions
chmod -R 755 storage/
chmod -R 755 storage/sessions/
chmod -R 755 storage/uploads/
chmod -R 755 storage/logs/

# Start development server
php -S localhost:8080 -t public
```

### Default Login
- **Username:** `admin`
- **Password:** `admin123`
- ⚠️ **Change the default password immediately after first login!**

### Apache Configuration

Make sure `mod_rewrite` is enabled and the `.htaccess` files are in place:

```apache
<VirtualHost *:80>
    DocumentRoot /path/to/manusclaw-php/public
    <Directory /path/to/manusclaw-php/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    root /path/to/manusclaw-php/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?url=$uri&$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ ^/(storage|config|app) {
        deny all;
    }
}
```

## Project Structure

```
manusclaw-php/
├── app/
│   ├── controllers/
│   │   ├── AdminController.php      # Admin panel logic
│   │   ├── AuthController.php       # Authentication
│   │   ├── BaseController.php       # Base controller
│   │   └── UserController.php       # User panel logic
│   ├── models/
│   │   ├── ActivityLog.php          # Activity logging
│   │   ├── Database.php             # PDO SQLite singleton
│   │   ├── LLMProvider.php          # LLM provider CRUD
│   │   ├── Setting.php              # System settings
│   │   ├── Task.php                 # Task management
│   │   └── User.php                 # User management
│   ├── services/
│   │   └── ManusClawBridge.php      # LLM API bridge
│   └── views/
│       ├── admin/                   # Admin panel views
│       ├── auth/                    # Login/Register/Profile
│       ├── layouts/                 # Main & Auth layouts
│       ├── partials/                # Reusable components
│       └── user/                    # User panel views
├── config/
│   └── database.php                 # DB config & constants
├── public/
│   ├── css/
│   │   └── app.css                  # Apple-style design system
│   ├── js/
│   │   └── app.js                   # Frontend JavaScript
│   ├── index.php                    # Router / entry point
│   └── .htaccess                    # URL rewriting
├── storage/
│   ├── manusclaw.db                 # SQLite database (auto-created)
│   ├── sessions/                    # PHP session files
│   ├── uploads/                     # User uploads
│   └── logs/                        # Application logs
├── .htaccess                        # Root security rules
└── .gitignore
```

## Security

- **CSRF Protection** — All forms include CSRF tokens
- **Password Hashing** — bcrypt via `password_hash()`
- **SQL Injection Prevention** — PDO prepared statements throughout
- **XSS Prevention** — All output escaped with `htmlspecialchars()`
- **Session Security** — Regenerated ID on login, secure storage
- **Directory Protection** — `.htaccess` blocks access to config/storage/app
- **Input Validation** — Server-side validation on all forms

## API Endpoints (AJAX)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/user/providers/test/{id}` | Test LLM provider connection |
| POST | `/admin/users/toggle-active/{id}` | Toggle user active status |
| DELETE | `/admin/users/delete/{id}` | Delete a user |
| POST | `/admin/settings/update` | Update system setting |

## Tech Stack

- **Backend:** PHP 8.1+ (vanilla, no framework)
- **Database:** SQLite via PDO
- **Frontend:** Vanilla JavaScript, CSS3
- **Design:** Custom Apple-inspired design system
- **Architecture:** MVC-like pattern with autoloading

## License

MIT License — feel free to use, modify, and distribute.

---

Built with ❤️ for the ManusClaw project

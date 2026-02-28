# Laravel Sail Quick Start - Partner Manager

## ✅ Sail is Configured!

Your partner-manager project is now ready to run in Docker containers.

---

## 🚀 Start the Project

### First Time Setup

```bash
cd /Users/gunnar.stoa/Library/CloudStorage/OneDrive-Anaplan/Desktop/partner-manager

# Start Sail containers
./vendor/bin/sail up -d

# Install PHP dependencies (inside container)
./vendor/bin/sail composer install

# Install Node dependencies
./vendor/bin/sail npm install

# Generate app key
./vendor/bin/sail artisan key:generate

# Run migrations
./vendor/bin/sail artisan migrate

# Build Vite assets
./vendor/bin/sail npm run build
```

### Daily Usage

```bash
# Start containers (detached mode)
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# View logs
./vendor/bin/sail logs -f
```

---

## 🔗 Access Your Application

Once Sail is running:

- **Application:** http://localhost:8083
- **Database:** localhost:33063 (from host machine)
  - Username: `sail`
  - Password: `password`
  - Database: `partner_manager`

---

## 📦 Sail Commands

### Artisan Commands
```bash
# Run any artisan command
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan tinker
```

### Composer Commands
```bash
# Install packages
./vendor/bin/sail composer install
./vendor/bin/sail composer require package-name
```

### NPM Commands
```bash
# Install dependencies
./vendor/bin/sail npm install

# Run dev server (with hot reload)
./vendor/bin/sail npm run dev

# Build for production
./vendor/bin/sail npm run build
```

### Database Commands
```bash
# Run migrations
./vendor/bin/sail artisan migrate

# Fresh migrate (drops all tables)
./vendor/bin/sail artisan migrate:fresh

# Seed database
./vendor/bin/sail artisan db:seed

# Fresh migrate + seed
./vendor/bin/sail artisan migrate:fresh --seed
```

### Shell Access
```bash
# Access Laravel container shell
./vendor/bin/sail shell

# Access MySQL container
./vendor/bin/sail mysql

# Root shell
./vendor/bin/sail root-shell
```

---

## ⚡ Create a Sail Alias (Recommended)

Add this to your `~/.zshrc` or `~/.bashrc`:

```bash
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

Then reload:
```bash
source ~/.zshrc  # or source ~/.bashrc
```

Now you can use:
```bash
sail up -d
sail artisan migrate
sail npm run dev
```

Instead of:
```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run dev
```

---

## 🔧 Ports Configuration

Your `.env` is configured with these ports (to avoid conflicts with partner-manager-2):

```env
APP_PORT=8083           # Application runs on http://localhost:8083
FORWARD_DB_PORT=33063   # MySQL accessible from host on port 33063
VITE_PORT=5174          # Vite dev server runs on port 5174
```

### Why Different Ports?

This allows you to run **both** partner-manager-2 and partner-manager simultaneously:

| Project | App URL | MySQL Port |
|---------|---------|------------|
| partner-manager-2 | http://localhost | 3306 |
| partner-manager | http://localhost:8083 | 33063 |

---

## 🐛 Troubleshooting

### Port Already in Use
If you get "port already allocated" error:

```bash
# Check what's using the port
lsof -i :8083

# Stop partner-manager-2 first if running
cd ../partner-manager-2
./vendor/bin/sail down

# Or change ports in partner-manager/.env
```

### Containers Not Starting
```bash
# Stop all containers
./vendor/bin/sail down

# Remove volumes and restart
./vendor/bin/sail down -v
./vendor/bin/sail up -d
```

### Permission Issues
```bash
# Fix storage permissions
./vendor/bin/sail artisan storage:link
sudo chmod -R 777 storage bootstrap/cache
```

### Database Connection Refused
Make sure:
1. Sail is running: `./vendor/bin/sail ps`
2. `.env` has `DB_HOST=mysql` (not localhost or 127.0.0.1)
3. Wait ~10 seconds for MySQL container to fully start

---

## 📊 Next Steps After Sail is Running

Once your Sail containers are up and running, follow the [MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md) to:

1. ✅ Copy your database migrations
2. ✅ Import your data from partner-manager-2
3. ✅ Copy models and controllers
4. ✅ Adapt SmartAdmin views to use your data

---

## 🎯 Quick Reference

```bash
# Start everything
./vendor/bin/sail up -d

# Stop everything
./vendor/bin/sail down

# Restart containers
./vendor/bin/sail restart

# View running containers
./vendor/bin/sail ps

# View logs
./vendor/bin/sail logs

# Access Laravel shell
./vendor/bin/sail shell

# Access MySQL
./vendor/bin/sail mysql

# Run tests
./vendor/bin/sail test

# Run Pint (code formatting)
./vendor/bin/sail pint
```

---

**You're all set!** 🎉

Start Sail with: `./vendor/bin/sail up -d`

Then visit: **http://localhost:8083**

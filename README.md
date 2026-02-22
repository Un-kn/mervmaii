# Mervmaii – Couple System (PHP)

**Mervmaii** = Rhea Mae & Mervin. Small system para sa inyong dalawa: upload ng pictures, love notes, at schedules/dates.

## Features
- **Login** – Mervmaii login with couple image sa gilid ng form (purple theme)
- **Dashboard** – Bilang ng photos, albums, love notes, at **months together** (anniversary-based)
- **Albums** – Add album, upload photos with caption
- **Love Notes** – Add/edit/delete notes
- **Announcements / Schedules** – Dates, lakad, reminders (with date & time, location)
- **Settings** – Change password, profile (display name, email, anniversary date), **Dark mode / Light mode**
- **Animations** – Fade-in, slide-up, hover effects

## Setup

1. **Database**
   - Create database named `mervmaii` (or import the SQL).
   - Import `mervmaii.sql` sa MySQL/MariaDB (phpMyAdmin or command line).
   - Edit `config/database.php` kung iba ang DB host/user/password.

2. **Login image**
   - Ilagay ang inyong couple picture sa:  
     `assets/images/login-couple.png`  
   - Kung wala, makikita pa rin ang login form; puwede ring gamitin ang existing image na nasa project.

3. **Default login** (after first run)
   - Username: `mervmaii`  
   - Password: `mervmaii123`  
   - **Palitan sa Settings → Change Password** after first login.

4. **Anniversary**
   - Sa **Settings → Profile**, itakda ang **Anniversary Date** (start of relationship) para tama ang “months together” sa dashboard.

## Requirements
- PHP 7.4+ (with PDO MySQL)
- MySQL or MariaDB
- Web server (XAMPP, WAMP, or Apache/Nginx)

## File structure
- `config/` – database, init (creates default user if empty)
- `includes/` – auth, header, footer
- `assets/css/style.css` – purple theme, dark/light mode
- `assets/js/main.js` – animations
- `uploads/` – uploaded photos (albums stored under `uploads/albums/<id>/`)
- `mervmaii.sql` – database schema and optional sample data

Enjoy, Rhea Mae & Mervin.

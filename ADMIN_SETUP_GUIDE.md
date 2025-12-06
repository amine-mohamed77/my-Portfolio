# 🚀 Admin Dashboard Setup Guide

Complete step-by-step guide to set up your portfolio admin dashboard.

## 📦 What You Got

Your portfolio now includes a powerful admin dashboard with:
- ✅ Skills management (CRUD)
- ✅ Projects management (CRUD with image upload)
- ✅ Secure authentication
- ✅ RESTful API endpoints
- ✅ Modern TailwindCSS UI
- ✅ Light/Dark mode
- ✅ Mobile responsive

## 🎯 Quick Start (5 Minutes)

### Step 1: Import Database

**Option A: Command Line**
```bash
mysql -u root -p < database.sql
```

**Option B: phpMyAdmin**
1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Create new database: `portfolio_db`
3. Click "Import" tab
4. Choose `database.sql` file
5. Click "Go"

### Step 2: Configure Database Connection

Edit: `admin/config/database.php`

```php
private $host = "localhost";
private $db_name = "portfolio_db";
private $username = "root";        // Your MySQL username
private $password = "";            // Your MySQL password
```

### Step 3: Create Uploads Folder

**Windows:**
```bash
mkdir uploads\projects
```

**Mac/Linux:**
```bash
mkdir -p uploads/projects
chmod 755 uploads/projects
```

### Step 4: Access Admin Dashboard

Open your browser and go to:
```
http://localhost/Portfolio/admin/login.php
```

**Default Credentials:**
- Username: `admin`
- Password: `Admin@123`

🔒 **Change this password immediately after first login!**

---

## 📂 File Structure

```
Portfolio/
├── admin/                          # Admin Dashboard
│   ├── api/
│   │   ├── skills.php             # Skills API endpoint
│   │   └── projects.php           # Projects API endpoint
│   ├── config/
│   │   └── database.php           # Database configuration
│   ├── includes/
│   │   └── auth.php              # Authentication helpers
│   ├── js/
│   │   └── dashboard.js          # Dashboard JavaScript
│   ├── .htaccess                  # Security configuration
│   ├── login.php                  # Login page
│   ├── logout.php                 # Logout handler
│   ├── dashboard.php              # Main dashboard
│   └── README.md                  # Admin documentation
├── js/
│   └── api-integration.js         # Portfolio API integration
├── uploads/
│   └── projects/                  # Project images storage
├── database.sql                   # Database schema
├── index.html                     # Your portfolio (unchanged)
└── ADMIN_SETUP_GUIDE.md          # This file
```

---

## 🔧 Advanced Configuration

### Changing MySQL Host/Port

If your MySQL is not on localhost:

```php
// admin/config/database.php
private $host = "127.0.0.1:3307";  // Custom port
// or
private $host = "your-remote-host.com";
```

### Enabling Dynamic Data Loading

To load skills and projects from database automatically:

1. Add to your `index.html` before `</body>`:
```html
<script src="js/api-integration.js"></script>
<script>
    // Enable dynamic loading
    window.portfolioAPI.enableDynamic();
</script>
```

2. Or manually trigger:
```javascript
// Load skills only
window.portfolioAPI.loadSkills();

// Load projects only
window.portfolioAPI.loadProjects();
```

### Customizing Upload Limits

Edit `admin/.htaccess`:
```apache
php_value upload_max_filesize 20M
php_value post_max_size 20M
```

Or edit your `php.ini`:
```ini
upload_max_filesize = 20M
post_max_size = 20M
```

---

## 🎨 Customization

### Adding New Skill Categories

1. Open `admin/dashboard.php`
2. Find line ~260 (skill category select)
3. Add your category:
```html
<option value="Mobile">Mobile</option>
<option value="Design">Design</option>
```

### Changing Dashboard Colors

Edit `admin/dashboard.php` TailwindCSS config:
```javascript
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: {
                    600: '#your-color-here',
                    700: '#your-darker-color',
                }
            }
        }
    }
}
```

---

## 🔐 Security Checklist

- [ ] Changed default admin password
- [ ] Updated database credentials
- [ ] Set correct file permissions (755 for directories, 644 for files)
- [ ] Enabled HTTPS in production
- [ ] Configured `.htaccess` properly
- [ ] Kept `config/` and `includes/` folders protected

---

## 🌐 API Usage Examples

### Get All Active Skills

```javascript
fetch('/admin/api/skills.php?active_only=true')
    .then(res => res.json())
    .then(data => {
        console.log(data.data.skills);
    });
```

### Get Featured Projects

```javascript
fetch('/admin/api/projects.php?featured_only=true&active_only=true')
    .then(res => res.json())
    .then(data => {
        console.log(data.data.projects);
    });
```

### Get Skills by Category

```javascript
fetch('/admin/api/skills.php?category=Frontend&active_only=true')
    .then(res => res.json())
    .then(data => {
        console.log(data.data.skills);
    });
```

---

## 📱 Mobile Access

The admin dashboard is fully responsive. Access it from any device:
- Desktop: Full featured experience
- Tablet: Optimized layout
- Mobile: Touch-friendly interface

---

## 🐛 Common Issues & Solutions

### Issue: "Connection failed"
**Solution:**
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database name is correct (`portfolio_db`)

### Issue: "Failed to upload image"
**Solution:**
```bash
# Check uploads folder exists and has correct permissions
mkdir -p uploads/projects
chmod 755 uploads/projects  # Linux/Mac
```

### Issue: "Unauthorized" on API calls
**Solution:**
- Log out and log in again
- Clear browser cookies
- Check session settings in `php.ini`

### Issue: Dashboard shows blank page
**Solution:**
- Check PHP error log
- Enable error display in PHP:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Issue: API returns empty data
**Solution:**
- Verify database has data
- Check API paths are correct
- Look for errors in browser console (F12)

---

## 🚀 Deployment to Production

### 1. Database Setup

```sql
-- Create production database
CREATE DATABASE portfolio_db_prod;

-- Import schema
mysql -u prod_user -p portfolio_db_prod < database.sql
```

### 2. Update Configuration

```php
// admin/config/database.php
private $host = "your-production-host";
private $db_name = "portfolio_db_prod";
private $username = "prod_user";
private $password = "secure_password";
```

### 3. Security Hardening

- Use strong passwords
- Enable HTTPS
- Update `.htaccess` with production rules
- Set restrictive file permissions
- Regular backups of database

### 4. Performance Optimization

- Enable PHP OPcache
- Use CDN for static assets
- Optimize images before upload
- Enable gzip compression

---

## 📊 Database Backup

### Backup Command

```bash
mysqldump -u root -p portfolio_db > backup_$(date +%Y%m%d).sql
```

### Restore Command

```bash
mysql -u root -p portfolio_db < backup_20240101.sql
```

---

## 🔄 Updating the System

### Adding New Fields to Skills

1. Modify database:
```sql
ALTER TABLE skills ADD COLUMN new_field VARCHAR(255);
```

2. Update API (`admin/api/skills.php`)
3. Update dashboard form (`admin/dashboard.php`)
4. Update JavaScript (`admin/js/dashboard.js`)

### Adding New Fields to Projects

Follow the same pattern as skills.

---

## 📞 Need Help?

1. **Check browser console** (F12) for JavaScript errors
2. **Check PHP error logs** for server-side issues
3. **Verify database connection** using phpMyAdmin
4. **Test API endpoints** using Postman or browser

---

## 🎓 Next Steps

1. ✅ Set up database ← Start here
2. ✅ Configure credentials
3. ✅ Login to dashboard
4. ✅ Add your real skills
5. ✅ Add your real projects
6. ✅ Upload project images
7. ✅ Test API integration
8. ✅ Deploy to production

---

## 📝 Notes

- The portfolio (`index.html`) remains **100% intact**
- All changes are in the `/admin` folder
- APIs are optional - portfolio works with static data
- Dashboard is separate from public portfolio
- No JavaScript frameworks required

---

**You now have a professional admin dashboard! 🎉**

Start by logging in at: `http://localhost/Portfolio/admin/login.php`

Good luck with your portfolio! 🚀

# Portfolio Admin Dashboard

Complete admin dashboard for managing portfolio skills and projects with PHP + MySQL backend.

## 🚀 Features

- ✅ **Secure Authentication** - Session-based login with bcrypt password hashing
- ✅ **Skills Management** - Full CRUD operations for skills
- ✅ **Projects Management** - Full CRUD with image upload support
- ✅ **Modern UI** - TailwindCSS with light/dark mode
- ✅ **RESTful API** - Clean API endpoints for frontend integration
- ✅ **Mobile Responsive** - Works perfectly on all devices
- ✅ **Dashboard Analytics** - Stats and overview cards

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PHP PDO extension enabled

## 🛠️ Installation

### 1. Database Setup

```bash
# Import the database schema
mysql -u root -p < ../database.sql
```

Or manually:
1. Open phpMyAdmin or MySQL Workbench
2. Create a new database named `portfolio_db`
3. Import the `database.sql` file

### 2. Configure Database Connection

Edit `admin/config/database.php`:

```php
private $host = "localhost";
private $db_name = "portfolio_db";
private $username = "your_username";  // Change this
private $password = "your_password";  // Change this
```

### 3. Set Permissions

```bash
# Create uploads directory and set permissions
mkdir -p uploads/projects
chmod 755 uploads/projects
```

### 4. Apache Configuration (if using .htaccess)

Create `.htaccess` in the admin folder:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /admin/
    
    # Deny access to config files
    <FilesMatch "^(config|includes)">
        Order allow,deny
        Deny from all
    </FilesMatch>
</IfModule>
```

## 🔐 Default Login Credentials

```
Username: admin
Password: Admin@123
```

**⚠️ IMPORTANT: Change the password immediately after first login!**

## 📁 Project Structure

```
admin/
├── api/
│   ├── skills.php          # Skills CRUD API
│   └── projects.php        # Projects CRUD API
├── config/
│   └── database.php        # Database configuration
├── includes/
│   └── auth.php           # Authentication helpers
├── js/
│   └── dashboard.js       # Dashboard JavaScript
├── login.php              # Login page
├── logout.php             # Logout handler
├── dashboard.php          # Main dashboard
└── README.md             # This file

uploads/
└── projects/             # Project images storage
```

## 🔗 API Endpoints

### Skills API (`api/skills.php`)

- **GET** `/api/skills.php` - Get all skills
- **GET** `/api/skills.php?id=1` - Get single skill
- **GET** `/api/skills.php?category=Frontend` - Filter by category
- **GET** `/api/skills.php?active_only=true` - Get active skills only
- **POST** `/api/skills.php` - Create new skill (requires auth)
- **PUT** `/api/skills.php` - Update skill (requires auth)
- **DELETE** `/api/skills.php?id=1` - Delete skill (requires auth)

### Projects API (`api/projects.php`)

- **GET** `/api/projects.php` - Get all projects
- **GET** `/api/projects.php?id=1` - Get single project
- **GET** `/api/projects.php?featured_only=true` - Get featured projects
- **GET** `/api/projects.php?active_only=true` - Get active projects only
- **POST** `/api/projects.php` - Create new project (requires auth)
- **PUT** `/api/projects.php` - Update project (requires auth)
- **DELETE** `/api/projects.php?id=1` - Delete project (requires auth)

## 🌐 Frontend Integration

### Fetch Skills (JavaScript)

```javascript
// Get all active skills
fetch('/admin/api/skills.php?active_only=true')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const skills = data.data.skills;
            // Display skills in your portfolio
            displaySkills(skills);
        }
    });
```

### Fetch Projects (JavaScript)

```javascript
// Get all active projects
fetch('/admin/api/projects.php?active_only=true')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const projects = data.data.projects;
            // Display projects in your portfolio
            displayProjects(projects);
        }
    });
```

## 🎨 Customization

### Add New Skill Categories

Edit `dashboard.php`, find the skill category select:

```html
<select id="skill-category">
    <option value="Frontend">Frontend</option>
    <option value="Backend">Backend</option>
    <option value="Database">Database</option>
    <option value="DevOps">DevOps</option>
    <option value="YourCategory">Your Category</option>
</select>
```

### Change Theme Colors

Edit TailwindCSS classes in `dashboard.php`:

```javascript
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#your-color',
            }
        }
    }
}
```

## 🔒 Security Features

1. **Password Hashing** - bcrypt with cost factor 10
2. **Prepared Statements** - All queries use PDO prepared statements
3. **Input Sanitization** - XSS prevention
4. **CSRF Protection** - Token validation (ready for implementation)
5. **Session Management** - Secure session handling
6. **File Upload Validation** - Type and size restrictions

## 📝 Database Schema

### Skills Table
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR, skill name)
- level (INT, 0-100 percentage)
- category (VARCHAR, Frontend/Backend/etc)
- icon_type (VARCHAR, text/emoji/svg)
- icon_value (TEXT, icon content)
- display_order (INT, sorting order)
- is_active (TINYINT, 0 or 1)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### Projects Table
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- title (VARCHAR, project title)
- description (TEXT, project description)
- tech_stack (JSON, array of technologies)
- image_path (VARCHAR, path to uploaded image)
- live_url (VARCHAR, live demo URL)
- github_url (VARCHAR, GitHub repository)
- display_order (INT, sorting order)
- is_featured (TINYINT, 0 or 1)
- is_active (TINYINT, 0 or 1)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

## 🐛 Troubleshooting

### "Connection failed"
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database name is correct

### "Failed to upload image"
- Check `uploads/projects/` directory exists
- Verify directory permissions (755 or 777)
- Check PHP `upload_max_filesize` in php.ini

### "Unauthorized" error
- Clear browser cookies
- Check session settings in php.ini
- Verify login credentials

### API returns empty data
- Check database has data
- Verify API file paths
- Check browser console for errors

## 📞 Support

For issues or questions, check:
1. Browser console for JavaScript errors
2. PHP error logs
3. Database connection status

## 📄 License

This admin dashboard is part of your portfolio project.

---

**Built with ❤️ for AMIN.DEV Portfolio**

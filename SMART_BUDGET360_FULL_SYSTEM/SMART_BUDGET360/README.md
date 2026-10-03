# SMART BUDGET360
## Sales & Marketing Budget Monitoring System

Production-style PHP/MySQL internal ERP application designed for XAMPP.

### Requirements
- XAMPP with Apache and MySQL/MariaDB
- PHP 8.0 or newer
- Modern browser
- Internet connection for Bootstrap, Chart.js, DataTables, Font Awesome and SweetAlert2 CDN assets

### Installation
1. Extract/copy the `SMART_BUDGET360` folder to:
   `C:\xampp\htdocs\SMART_BUDGET360`
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Import `database/smart_budget360.sql`. The SQL file creates and selects the `smart_budget360` database automatically.
5. Open: `http://localhost/SMART_BUDGET360`

### Default Login
- Username: `admin`
- Password: `admin123`

Change the default administrator password after installation from **Administration → Users & Roles → Edit User**.

### Included Modules
- Executive analytics dashboard
- Budget Management with CRUD, categories, reports and DataTables exports
- Expense Monitoring with receipt upload and tracking
- Two-stage Department Manager → Finance approval workflow with history
- Sales targets, monthly entries, rankings and reports
- Marketing campaigns, campaign expenses and ROI analysis
- Reporting Center with Budget, Expense, Sales, Marketing ROI and Monthly Financial reports
- CSV, Excel and Print/PDF exports
- User/role management, departments, audit logs and settings
- Password hashing, PDO prepared statements, CSRF protection, secure sessions, RBAC, input validation and activity logging

### Role Notes
- **Administrator:** full access
- **Finance Manager:** budgets, expenses, Finance approval stage, reports
- **Sales Manager:** sales, department-level approvals for own department, reports
- **Marketing Manager:** campaigns, department-level approvals for own department, reports
- **Staff:** dashboard and own expense requests
- **Viewer:** reports only

### Sample Accounts
The SQL contains sample manager/staff/viewer accounts and transactional data for dashboard visualization. The production login to use initially is the administrator account above.

### Configuration
- Database connection: `config/database.php`
- Application base URL: `config/app.php`
- If you rename the project folder, update `BASE_URL` in `config/app.php`.
- Receipt files are stored in `assets/uploads/receipts/`.

### Security / Deployment
This build is suitable for a protected internal XAMPP environment. Before exposing it to the public internet, use HTTPS, disable directory listing, restrict database privileges, configure production error logging, perform dependency/vendor review, and place uploads behind stricter web-server rules or object storage.

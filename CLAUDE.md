# CLAUDE.md - AI Assistant Guide for MR HASAR DANIŞMANLIK

## Project Overview

**Project Name:** MR HASAR DANIŞMANLIK (MR Loss Consulting)
**Tagline:** HERZAMAN FARKEDER (Always Makes a Difference)
**Type:** Web-based Enterprise Management System
**Version:** v11.0
**Language:** Turkish (interface and documentation)
**Primary Domain:** Insurance claim consulting and fleet management

This is a PHP-based web application for an insurance consulting company handling:
- **ADK (Araç Değer Kaybı):** Vehicle depreciation claims
- **BH (Beden Hasarı):** Bodily injury claims
- Client case management, financial operations, personnel management, and internal communications

---

## Technology Stack

### Backend
- **Language:** PHP 7+ (procedural, no framework)
- **Database:** MySQL 8.0+ with utf8mb4 charset
- **Database Abstraction:** PDO with prepared statements
- **Session:** PHP native sessions
- **Security:** bcrypt password hashing, CSRF tokens

### Frontend
- **CSS Framework:** Bootstrap 5.3.2
- **Icons:** Bootstrap Icons 1.11.1
- **JavaScript:** jQuery 3.7.1
- **Data Tables:** DataTables 1.13.7
- **Dropdowns:** Select2 4.1.0
- **Date Picker:** Flatpickr (Turkish locale)
- **Charts:** Chart.js
- **Alerts:** SweetAlert2 11

### CDN Resources
All major frontend libraries are loaded from CDN for lightweight deployment.

---

## Project Structure

```
MRSOFT/
├── README.md
├── CLAUDE.md                    # This file
└── public_html/
    └── finansal/
        └── admin/               # Main application directory
            ├── config.php       # Central configuration (10.5 KB)
            ├── index.php        # Router/dispatcher (1.4 KB)
            ├── login.php        # Authentication UI (7.2 KB)
            ├── logout.php       # Session termination
            ├── db/
            │   └── schema.sql   # Database schema (903 lines)
            ├── includes/
            │   ├── header.php   # Navigation sidebar
            │   └── footer.php   # Scripts and footer
            ├── assets/
            │   ├── css/
            │   │   └── style.css    # Main stylesheet (692 lines)
            │   └── js/
            │       └── main.js      # Client utilities (364 lines)
            └── modules/         # 31 feature modules
                ├── dashboard.php
                ├── dosyalar.php
                ├── dosya_ekle.php
                ├── dosya_detay.php
                ├── crm.php
                └── ... (26 more modules)
```

### Key Directories

| Directory | Purpose |
|-----------|---------|
| `/admin/` | Main application root |
| `/admin/config.php` | Database connection, helper functions |
| `/admin/db/` | Database schema |
| `/admin/includes/` | Reusable header/footer components |
| `/admin/modules/` | 31 feature modules (~5,600 lines) |
| `/admin/assets/` | CSS and JavaScript files |
| `/admin/uploads/` | File storage for case documents |

---

## Database Schema

The database contains **30+ tables** organized by domain:

### User & Authentication (3 tables)
- `users` - User accounts with roles (admin, manager, user, lawyer)
- `user_permissions` - Role-based permissions
- Default admin: username='admin', password='admin123'

### Core Case Management (5 tables)
- `cases` - Main case records with comprehensive claim info
- `case_documents` - Attached documents/evidence
- `case_expenses` - Case-related costs
- `case_payments` - Incoming payments (collections)
- `case_activities` - Activity log per case

### Reference Data (8 tables)
- `insurance_companies` - 40+ Turkish insurance companies
- `stages` - 10 workflow stages (DOSYA AÇILDI → ÖDEME ALINDI)
- `lawyers`, `referrers`, `partners`, `personnel`
- `expense_types` (22 types), `document_types` (25+ types)

### Financial Management (8 tables)
- `cashes` - Bank accounts and cash registers
- `cash_transactions` - All cash movements
- `partner_transactions`, `cari_accounts`, `cari_transactions`
- `income_expense`, `salary_payments`, `settings`

### CRM & Communication (5 tables)
- `crm_leads`, `crm_notes`, `messages`, `requests`, `calendar_events`

### Specialized (4 tables)
- `ihbar_foyleri` - Report forms (İhbar Föyü)
- `adk_vehicle_brands`, `adk_vehicle_models`
- `backup_logs`, `activity_logs`

---

## Routing System

The application uses query parameter-based routing:

```
index.php?page=MODULE_NAME
```

### Available Routes (31 modules)

| Route | Purpose |
|-------|---------|
| `?page=dashboard` | Analytics overview |
| `?page=dosyalar` | Case listing with filters |
| `?page=dosya_ekle` | Create/edit case |
| `?page=dosya_detay` | Case details view |
| `?page=crm` | Lead management |
| `?page=adk_hesaplama` | Vehicle loss calculations |
| `?page=maluliyet_hesaplama` | Bodily injury calculations |
| `?page=ihbar_foyu` | Report form generation |
| `?page=cari` | Accounts receivable |
| `?page=kasa` | Cash/bank management |
| `?page=gelir_gider` | Income/expense tracking |
| `?page=ortaklar` | Partner management |
| `?page=rapor_*` | Various reports |
| `?page=kullanici_yonetimi` | User management |
| `?page=ayarlar` | System settings |

### Query Parameters
- `?id=N` - Record ID for editing/viewing
- `?delete=N` - Trigger delete with confirmation
- `?convert=N` - Lead-to-case conversion
- `?type=X, ?stage=X, ?status=X, ?search=TEXT` - Filters

---

## Configuration

### config.php Constants
```php
// Database
DB_HOST = 'localhost'
DB_NAME = 'mrhasard_finansal'
DB_CHARSET = 'utf8mb4'

// Application
APP_NAME = 'MR HASAR DANIŞMANLIK'
APP_VERSION = 'v11.0'
APP_URL = 'https://mrhasardanismanlik.com/finansal/admin/'

// File handling
MAX_FILE_SIZE = 10 * 1024 * 1024 (10MB)
ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx']

// Timezone
Europe/Istanbul
```

### Runtime Settings (database `settings` table)
- company_name, company_slogan, company_phone, company_email
- adk_default_rate, bh_default_rate, pmf_discount_rate
- file_no_prefix_adk, file_no_prefix_bh

---

## Helper Functions (config.php)

### Security & Authentication
- `requireLogin()` - Redirect if not authenticated
- `isLoggedIn()` - Check authentication status
- `hasRole($role)` - Check user role
- `hasPermission($permission)` - Check specific permission
- `generateCSRFToken()` / `verifyCSRFToken()` - CSRF protection

### Input Handling
- `clean($data)` - Sanitize input (trim, stripslashes, htmlspecialchars)
- `e($str)` - XSS-safe output escaping

### Data Formatting
- `formatMoney($amount)` - Currency formatting (Turkish Lira)
- `formatDate($date)` - Date formatting (d.m.Y)
- `formatDateTime($datetime)` - Date/time formatting
- `validateTC($tc)` - Turkish ID number validation

### Database Operations
- `logActivity($action, $table, $recordId, $oldData, $newData)` - Audit trail
- `generateFileNo($type)` - Auto-increment case numbering
- `getDashboardStats()` - Business metrics
- `paginate($total, $perPage, $currentPage)` - Pagination helper
- `getSetting($key)` / `setSetting($key, $value)` - Settings management
- `getDropdownData($table, $conditions)` - Dynamic select options

### File Handling
- `uploadFile($file, $destination)` - File upload with validation

---

## Code Patterns

### Module Structure
All modules follow this pattern:
```php
<?php
// 1. Authorization check
requireLogin();
if (!hasRole('admin')) redirect('index.php?page=dashboard');

// 2. Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token']);
    // Validate and process data
    // Database operations
    setFlashMessage('success', 'İşlem başarılı');
    redirect('index.php?page=module');
}

// 3. Handle GET requests (display data)
$data = $pdo->query("SELECT * FROM table")->fetchAll();
?>

<!-- 4. HTML output with Bootstrap -->
<div class="container-fluid">
    <!-- Form or data table -->
</div>
```

### CRUD Operations
- **Create/Update:** POST to self, redirect after success
- **Read:** GET with optional filters
- **Delete:** GET with `?delete=ID`, confirmation modal
- Uses soft delete (is_active flag) for audit trail

### Form Pattern
```php
<form method="POST" class="ajax-form">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
    <!-- Form fields -->
    <button type="submit">Kaydet</button>
</form>
```

### Database Access Pattern
```php
// Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ?");
$stmt->execute([$id]);
$case = $stmt->fetch(PDO::FETCH_ASSOC);
```

---

## Security Features

### Input Protection
- `clean()` function for all user input
- `e()` function for output escaping
- Parameterized queries (PDO prepared statements)

### Authentication
- bcrypt password hashing (`password_hash`/`password_verify`)
- PHP native sessions
- Last login tracking

### CSRF Protection
- Token generation in forms
- Token verification on POST requests

### Audit Trail
- `activity_logs` table tracks all user actions
- Captures: user_id, action, table_name, record_id, old_data, new_data, ip_address

### File Upload Security
- Max size: 10 MB
- Whitelist extensions only
- Unique filename generation

---

## Naming Conventions

| Type | Convention | Example |
|------|-----------|---------|
| Tables | snake_case (English) | `insurance_companies`, `case_expenses` |
| Columns | snake_case | `client_name`, `accident_date` |
| Functions | camelCase | `getDashboardStats()`, `validateTC()` |
| Variables | camelCase or snake_case | `$totalCases`, `$stage_name` |
| Constants | UPPER_SNAKE_CASE | `DB_HOST`, `MAX_FILE_SIZE` |
| CSS Classes | kebab-case | `.page-header`, `.stat-card` |
| JS Functions | camelCase | `isValidTC()`, `formatMoney()` |

---

## Development Guidelines

### When Adding New Features
1. Create new module in `/modules/` directory
2. Add route to whitelist in `index.php`
3. Follow existing module structure pattern
4. Use prepared statements for all database queries
5. Include CSRF token in all forms
6. Use `clean()` for input, `e()` for output
7. Add activity logging for auditable actions

### When Modifying Existing Code
1. Check for related code in other modules
2. Maintain existing naming conventions
3. Test with different user roles
4. Verify CSRF protection is intact
5. Update activity logging if needed

### Database Changes
1. Update `db/schema.sql` with new DDL
2. Add indexes for frequently queried columns
3. Use foreign keys with appropriate cascading
4. Use DATETIME with DEFAULT CURRENT_TIMESTAMP

### UI/UX Guidelines
1. Use Bootstrap 5 grid system
2. Use DataTables for sortable/searchable lists
3. Use Select2 for enhanced dropdowns
4. Use Flatpickr for date selection
5. Use SweetAlert2 for confirmations
6. Flash messages for user feedback

---

## Common Tasks

### Adding a New Module
```php
// modules/new_module.php
<?php
requireLogin();
// Module logic here
?>
<div class="container-fluid">
    <div class="page-header">
        <h4>Module Title</h4>
    </div>
    <!-- Content -->
</div>
```

Then add to `index.php` whitelist:
```php
$allowed_pages = ['dashboard', ..., 'new_module'];
```

### Adding a New Database Table
1. Add CREATE TABLE statement to `db/schema.sql`
2. Include appropriate foreign keys and indexes
3. Add reference data INSERT statements if needed

### Creating Reports
1. Create module in `/modules/rapor_*.php`
2. Use aggregation queries for summaries
3. Include export functionality if needed
4. Use Chart.js for visualizations

---

## Important Notes for AI Assistants

### Architecture Awareness
- This is a **procedural PHP application** (no OOP/MVC framework)
- All code is in a single namespace (global functions)
- No autoloading - files are included directly

### Language Consideration
- Interface is in **Turkish**
- Variable names mix Turkish and English
- Comments are in Turkish
- User-facing strings should remain in Turkish

### Security Critical
- Always use prepared statements for SQL
- Always validate and sanitize user input
- Always include CSRF tokens in forms
- Never expose sensitive data in URLs or logs

### Testing
- No automated testing framework exists
- Manual testing required for all changes
- Test with different user roles (admin, manager, user, lawyer)

### Performance
- Large datasets may need pagination
- Use indexes for frequently filtered columns
- CDN-based libraries reduce server load

---

## File Reference Quick Guide

| File | Lines | Purpose |
|------|-------|---------|
| config.php | ~300 | Configuration, DB connection, 30+ helper functions |
| index.php | ~50 | Router with 31 allowed pages |
| login.php | ~200 | Authentication UI |
| schema.sql | ~900 | Complete database DDL |
| style.css | ~700 | Custom theme and utilities |
| main.js | ~360 | Client-side validation and utilities |

---

## Troubleshooting

### Common Issues
1. **Session expired:** Check PHP session configuration
2. **CSRF error:** Regenerate token, check form submission
3. **Permission denied:** Verify user role and permissions
4. **File upload failed:** Check file size and extension
5. **Database error:** Check PDO error mode and query syntax

### Debug Tips
- Check `error_reporting` in config.php
- Review `activity_logs` table for user actions
- Use browser dev tools for JavaScript errors
- Check PHP error logs for backend issues

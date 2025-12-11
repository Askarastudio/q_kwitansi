# Q-Berkas - Installation Guide

## Quick Start

### 1. Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server or PHP built-in server

### 2. Installation Steps

#### Step 1: Clone the Repository
```bash
git clone https://github.com/Askarastudio/q_kwitansi.git
cd q_kwitansi
```

#### Step 2: Create Database
```bash
mysql -u root -p
```

In MySQL prompt:
```sql
CREATE DATABASE q_berkas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### Step 3: Import Schema
```bash
mysql -u root -p q_berkas < database/schema.sql
```

#### Step 4: Configure Database Connection
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Your MySQL username
define('DB_PASS', '');              // Your MySQL password
define('DB_NAME', 'q_berkas');
```

#### Step 5: Run the Application

**Using PHP Built-in Server (Development):**
```bash
php -S localhost:8000
```

Then open browser: `http://localhost:8000`

**Using Apache:**
- Point DocumentRoot to the project folder
- Or create a virtual host

**Using XAMPP/WAMP:**
- Copy project to htdocs folder
- Access via `http://localhost/q_kwitansi`

### 3. Login

Default credentials:
- **Username:** admin
- **Password:** admin123

**IMPORTANT:** Change the default password after first login!

## Features Overview

### Master Data Management
1. **Customers**: Manage customer information
   - Add, edit, delete customers
   - Store contact information, address, etc.

2. **Items/Services**: Manage products and services
   - Support for both goods and services
   - Set prices, units, descriptions

3. **Projects**: Track projects/jobs
   - Link projects to customers
   - Track project status and value

### Document Generation
1. **Invoices**: Create professional invoices
   - Add multiple items
   - Auto-calculate totals
   - Support tax and discounts
   - Export to PDF

2. **Receipts (Kwitansi)**: Generate payment receipts
   - Link to customers and projects
   - Multiple payment methods
   - Professional PDF format

3. **Delivery Notes (Faktur Barang)**: Create delivery documents
   - Track goods shipment
   - Link to projects and customers
   - PDF export for printing

4. **Letters**: Generate official letters
   - Multiple letter types
   - Professional formatting
   - PDF export

### Document Integration
- All documents linked to customers
- Optional project association
- Auto-populate from master data
- Inter-document relationships

## Usage Guide

### Creating an Invoice

1. Go to **Dokumen > Invoice**
2. Click **Buat Invoice Baru**
3. Fill in customer and project information
4. Add items (select from master or manual input)
5. System automatically calculates totals
6. Add tax/discount if needed
7. Click **Simpan & Lihat PDF**
8. Invoice is saved and PDF opens in new tab

### Creating a Receipt

1. Go to **Dokumen > Kwitansi**
2. Click **Buat Kwitansi**
3. Select customer and amount
4. Choose payment method
5. Add description
6. Click **Simpan**
7. Click PDF icon to view/print

### Managing Master Data

All master data follows the same pattern:
1. Navigate to the respective menu
2. Click **Tambah** button
3. Fill in the form
4. Click **Simpan**
5. Use **Edit** or **Delete** buttons as needed

## Troubleshooting

### Database Connection Error
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

### Cannot Login
- Ensure database schema is imported correctly
- Default user should exist in `users` table
- Check if session is working (session.save_path writable)

### PDF Not Displaying
- Check if pop-up blocker is enabled
- Try opening in new tab
- Ensure PHP output buffering is working

## Security Notes

1. **Change Default Password**: Always change admin password after installation
2. **Database Credentials**: Keep database credentials secure
3. **Production Setup**: 
   - Disable error display in production
   - Use HTTPS
   - Regular backups

## Customization

### Company Information
Edit the following files to customize company info in PDFs:
- `receipt_pdf.php`
- `invoice_pdf.php`
- `letter_pdf.php`
- `delivery_note_pdf.php`

### Colors/Branding
Main color scheme in:
- `app/views/layouts/main.php` (--primary-color, --secondary-color variables)
- PDF files (inline styles)

### Email Configuration
For future email features, configure in `config/config.php`

## Support

For issues or questions:
- Check the README.md file
- Create an issue on GitHub
- Contact: Askarastudio

## License

This project is created for business use. Feel free to customize and deploy.

---

**Version:** 1.0.0  
**Last Updated:** December 2025  
**Developed by:** Askarastudio

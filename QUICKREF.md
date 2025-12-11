# Q-Berkas Quick Reference

## File Structure Overview

```
q_kwitansi/
├── Authentication & Core
│   ├── index.php              # Entry point (redirects to login/dashboard)
│   ├── login.php              # Login handler & form
│   ├── logout.php             # Logout handler
│   └── dashboard.php          # Main dashboard with statistics
│
├── Master Data Management
│   ├── customers.php          # Customer CRUD operations
│   ├── items.php              # Items/Services CRUD
│   └── projects.php           # Project/Job CRUD
│
├── Documents - Management
│   ├── invoices.php           # Invoice listing
│   ├── invoice_form.php       # Invoice creation form
│   ├── invoice_view.php       # Invoice detail view
│   ├── receipts.php           # Receipt listing & form
│   ├── delivery_notes.php     # Delivery note listing
│   ├── delivery_note_form.php # Delivery note form
│   └── letters.php            # Letter listing & form
│
├── Documents - PDF Export
│   ├── invoice_pdf.php        # Invoice PDF generator
│   ├── receipt_pdf.php        # Receipt PDF generator
│   ├── delivery_note_pdf.php  # Delivery note PDF
│   └── letter_pdf.php         # Letter PDF generator
│
├── Configuration
│   ├── config/config.php      # General configuration
│   └── config/database.php    # Database connection
│
├── Database
│   └── database/schema.sql    # Full database schema + sample data
│
└── Views
    ├── app/views/auth/login.php       # Login view
    └── app/views/layouts/main.php     # Main layout template
```

## Database Tables

1. **users** - User accounts with authentication
2. **customers** - Customer master data
3. **items** - Goods and services catalog
4. **projects** - Project/job tracking
5. **invoices** - Invoice headers
6. **invoice_items** - Invoice line items
7. **receipts** - Payment receipts
8. **delivery_notes** - Delivery document headers
9. **delivery_note_items** - Delivery document items
10. **letters** - Official letters/correspondence

## Key Features by Module

### Dashboard (dashboard.php)
- Total customers, projects, invoices count
- Project total value
- Recent invoices list
- Recent projects list
- Quick access cards for all modules

### Customers Module (customers.php)
- Add new customers with full details
- Edit existing customer information
- Delete customers (with constraint checks)
- View customer list with DataTables
- Fields: code, name, address, city, phone, email, contact person

### Items Module (items.php)
- Manage goods and services
- Set prices and units
- Type classification (goods/service)
- Fields: code, name, type, unit, price, description

### Projects Module (projects.php)
- Create and track projects
- Link to customers
- Track status (planning, ongoing, completed, cancelled)
- Set project value and dates
- Fields: code, name, customer, description, dates, status, value

### Invoice Module (invoices.php, invoice_form.php, invoice_view.php, invoice_pdf.php)
- Create invoices with multiple items
- Auto-calculate subtotal, tax, discount, total
- Link to customer and project
- Status tracking (draft, sent, paid, cancelled)
- Professional PDF export
- View invoice details

### Receipt Module (receipts.php, receipt_pdf.php)
- Generate payment receipts
- Multiple payment methods
- Link to customer, project, and invoice (optional)
- Professional kwitansi format PDF
- Fields: number, date, customer, amount, payment method

### Delivery Note Module (delivery_notes.php, delivery_note_form.php, delivery_note_pdf.php)
- Create goods delivery documents
- Multiple items per document
- Track sender and receiver
- Link to customer and project
- PDF export for printing

### Letter Module (letters.php, letter_pdf.php)
- Generate official letters
- Multiple letter types (offer, order, agreement, etc.)
- Link to customer and project (optional)
- Professional letterhead format
- Fields: number, date, subject, recipient, content, type

## Common Workflows

### Create Invoice Workflow
1. Navigate to Dokumen > Invoice
2. Click "Buat Invoice Baru"
3. Select customer (auto-fills customer data)
4. Optional: Select project
5. Add items:
   - Select from master items (auto-fills description, unit, price)
   - OR manual input
6. Adjust quantities
7. Add tax/discount if needed
8. System auto-calculates totals
9. Add notes and set status
10. Click "Simpan & Lihat PDF"
11. PDF opens in new tab for printing

### Create Receipt from Invoice
1. View invoice details
2. Note invoice number and amount
3. Go to Dokumen > Kwitansi
4. Click "Buat Kwitansi"
5. Select same customer
6. Optional: Link to same project
7. Enter amount received
8. Select payment method
9. Reference invoice number in description
10. Save and generate PDF

### Link Documents to Project
- All documents support optional project linking
- When project is selected:
  - Customer is often pre-selected
  - Documents are tracked under project
  - Easy reporting by project

## Default Login

```
Username: admin
Password: admin123
```

**⚠️ IMPORTANT: Change password after first login!**

## URL Routes (if using PHP built-in server on port 8000)

- Login: http://localhost:8000/login.php
- Dashboard: http://localhost:8000/dashboard.php
- Customers: http://localhost:8000/customers.php
- Items: http://localhost:8000/items.php
- Projects: http://localhost:8000/projects.php
- Invoices: http://localhost:8000/invoices.php
- Receipts: http://localhost:8000/receipts.php
- Delivery Notes: http://localhost:8000/delivery_notes.php
- Letters: http://localhost:8000/letters.php

## PDF Features

All PDF exports include:
- Professional formatting
- Company branding (customizable)
- Complete document details
- Print-friendly layout
- Auto-generated from database
- One-click generation

## Sample Data Included

The schema.sql includes:
- 1 admin user
- 3 sample customers
- 5 sample items (mix of goods and services)
- 3 sample projects

## Tips & Best Practices

1. **Always link documents to projects** when applicable for better tracking
2. **Use code conventions** for easy searching (e.g., CUST001, ITM001, PRJ001)
3. **Regular backups** of the database
4. **Update company info** in PDF files before production use
5. **Change default password** immediately after installation

## Support Files

- **README.md** - Complete feature overview and introduction
- **INSTALL.md** - Step-by-step installation guide
- **QUICKREF.md** - This file - quick reference guide

---

**Version:** 1.0.0  
**Last Updated:** December 2025

# Q-Berkas - Sistem Manajemen Dokumen & Invoice

Aplikasi web-based berbasis PHP untuk mengelola dokumen bisnis seperti Invoice, Kwitansi, Faktur Barang, dan Surat secara otomatis dan terintegrasi dengan Project/Pekerjaan.

## Fitur Utama

### 1. **Autentikasi & Dashboard**
- Login dengan tampilan modern dan menarik menggunakan desain terinspirasi Mazer
- Dashboard dengan statistik real-time
- Sidebar navigasi dengan gradient warna yang elegan
- Session management yang aman

### 2. **Master Data**
- **Customer**: Kelola data pelanggan dengan informasi lengkap (kode, nama, alamat, kontak)
- **Barang/Jasa**: Manajemen produk dan layanan dengan tipe, satuan, dan harga
- **Project/Pekerjaan**: Tracking project dengan status, timeline, dan nilai project

### 3. **Dokumen Otomatis**
- **Invoice**: Generate invoice dengan item detail dan kalkulasi otomatis
- **Kwitansi**: Buat kwitansi pembayaran dengan berbagai metode pembayaran
- **Faktur Barang/Surat Jalan**: Dokumen pengiriman barang
- **Surat**: Template surat untuk berbagai keperluan bisnis

### 4. **Integrasi Data**
- Semua dokumen terintegrasi dengan data customer dan project
- Auto-populate data dari master data
- Tracking hubungan antar dokumen (invoice-kwitansi, project-dokumen)

## Teknologi

- **Backend**: PHP 7.4+ dengan PDO untuk database
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 5, jQuery, DataTables
- **Template**: Desain modern terinspirasi Mazer Admin Template
- **Icons**: Bootstrap Icons

## Instalasi

### Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Web Server (Apache/Nginx)
- PHP Extensions: PDO, pdo_mysql

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/Askarastudio/q_kwitansi.git
   cd q_kwitansi
   ```

2. **Konfigurasi Database**
   - Buat database MySQL baru:
     ```sql
     CREATE DATABASE q_berkas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```
   
   - Import skema database:
     ```bash
     mysql -u root -p q_berkas < database/schema.sql
     ```

3. **Konfigurasi Aplikasi**
   - Edit file `config/database.php` sesuai dengan konfigurasi database Anda:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'q_berkas');
     ```

4. **Setup Web Server**
   
   **Untuk Apache:**
   - Pastikan DocumentRoot mengarah ke folder project
   - Atau buat virtual host baru
   
   **Untuk Development (PHP Built-in Server):**
   ```bash
   php -S localhost:8000
   ```

5. **Akses Aplikasi**
   - Buka browser dan akses: `http://localhost:8000` atau sesuai konfigurasi Anda
   - Login dengan kredensial default:
     - Username: `admin`
     - Password: `admin123`

## Struktur Folder

```
q_kwitansi/
├── app/
│   ├── controllers/      # Controller files (future)
│   ├── models/          # Model files (future)
│   └── views/           # View templates
│       ├── auth/        # Login pages
│       ├── layouts/     # Layout templates
│       └── ...          # Feature-specific views
├── config/              # Configuration files
│   ├── config.php       # General configuration
│   └── database.php     # Database configuration
├── database/            # Database files
│   └── schema.sql       # Database schema & sample data
├── public/              # Public assets
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   ├── images/         # Images
│   └── uploads/        # Uploaded files
├── customers.php        # Customer management
├── items.php           # Items/Services management
├── projects.php        # Project management
├── invoices.php        # Invoice management
├── receipts.php        # Receipt (Kwitansi) management
├── delivery_notes.php  # Delivery notes management
├── letters.php         # Letter management
├── dashboard.php       # Main dashboard
├── login.php          # Login page
├── logout.php         # Logout handler
└── index.php          # Entry point
```

## Penggunaan

### 1. Login
- Akses halaman login
- Masukkan username dan password
- Sistem akan mengarahkan ke dashboard

### 2. Kelola Master Data
- Buka menu Master Data dari sidebar
- Tambah, edit, atau hapus data customer, barang/jasa, dan project
- Data ini akan digunakan untuk membuat dokumen

### 3. Buat Dokumen
- Pilih jenis dokumen dari menu Dokumen
- Klik tombol "Tambah" atau "Buat Baru"
- Isi form dengan memilih dari master data yang sudah ada
- Sistem akan otomatis mengisi informasi terkait
- Simpan dan export ke PDF jika diperlukan

### 4. Tracking & Laporan
- Dashboard menampilkan statistik real-time
- Lihat dokumen terbaru dan status project
- Gunakan fitur pencarian dan filter pada tabel data

## Fitur Keamanan

- Password hashing menggunakan bcrypt
- Session management yang aman
- SQL injection protection dengan PDO prepared statements
- XSS protection dengan htmlspecialchars
- Login authentication pada setiap halaman

## Pengembangan Lebih Lanjut

Aplikasi ini dapat dikembangkan lebih lanjut dengan:
- Export ke PDF untuk semua dokumen
- Multi-company/tenant support
- Role-based access control (RBAC)
- Email notification
- Laporan keuangan
- Dashboard analytics yang lebih detail
- API untuk integrasi dengan sistem lain

## Kontribusi

Kontribusi sangat diterima! Silakan fork repository ini dan buat pull request dengan perubahan Anda.

## Lisensi

Project ini dibuat untuk keperluan bisnis dan dapat digunakan sesuai kebutuhan.

## Support

Untuk pertanyaan atau dukungan, silakan buat issue di GitHub repository ini.

## Credit

Developed by Askarastudio
- GitHub: [Askarastudio](https://github.com/Askarastudio)
- Template Design: Inspired by Mazer Admin Template

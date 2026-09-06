# PANDUAN LENGKAP CI/CD & AUTO-DEPLOYMENT INVENTARIS

Dokumen ini menjelaskan arsitektur dan langkah konfigurasi Continuous Integration & Continuous Deployment (CI/CD) untuk repositori **Inventaris Adiwiyata**.

---

## 1. Arsitektur Pipeline CI/CD

Pipeline diatur melalui GitHub Actions pada file [`.github/workflows/ci-cd.yml`](../.github/workflows/ci-cd.yml) yang akan berjalan otomatis setiap kali ada event **`push`** atau **`pull_request`** ke branch `main`.

```
                    ┌─────────────────────────────────────────┐
                    │       Developer git push ke main        │
                    └────────────────────┬────────────────────┘
                                         │
                                         ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           JOB 1: Test & Quality Gate                            │
│  1. Checkout Code                                                               │
│  2. Setup PHP 8.2 & Extensions (mysqli, pdo_mysql, gd, mbstring)                 │
│  3. PHP Syntax Verification (Linter seluruh file *.php)                         │
│  4. Spin-up MySQL 8.0 Container & Auto-Import database.sql                      │
│  5. Jalankan RBAC Security Test Suite (84 Test Assertion)                       │
│  6. Jalankan Dashboard Role-Aware Test Suite (25 Test Assertion)                │
│  7. Jalankan Responsive Layout Audit (9 Viewport 320px - 1920px)                │
└────────────────────────────────────────┬────────────────────────────────────────┘
                                         │ Lulus Semua Uji (Green)
                                         ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                     JOB 2: Automated Server Deployment                          │
│                                                                                 │
│   Apakah GitHub Secrets dikonfigurasi?                                          │
│   ├── [Opsi A: SSH]     -> appleboy/ssh-action (Deploy via SSH ke VPS)          │
│   ├── [Opsi B: Webhook] -> Trigger deploy.php (Deploy via Webhook cPanel/Hosting)│
│   └── [Belum disetting] -> Memberikan notifikasi petunjuk tanpa menggagalkan CI │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Pilihan Metode Deployment

Anda dapat memilih salah satu dari dua metode deployment berikut sesuai infrastruktur hosting Anda:

### Opsi A: SSH Direct Deployment (Direkomendasikan untuk VPS / Cloud Server)
Sangat cocok untuk **Ubuntu, Debian, CentOS, AlmaLinux, DigitalOcean, AWS EC2, Linode, IdCloudHost**, dll.

#### Konfigurasi GitHub Secrets:
Buka repositori GitHub Anda: **Settings** -> **Secrets and variables** -> **Actions** -> klik **New repository secret**:

| Nama Secret | Contoh Nilai | Keterangan |
|-------------|--------------|------------|
| `SERVER_HOST` | `103.187.xxx.xxx` atau `inventory.domainanda.com` | IP Publik atau Domain Server |
| `SERVER_USER` | `root` atau `ubuntu` atau `deployer` | Username login SSH |
| `SERVER_SSH_KEY` | `-----BEGIN OPENSSH PRIVATE KEY----- ...` | Private key SSH (`id_rsa` / `id_ed25519`) |
| `SERVER_PATH` | `/var/www/html/inventory` atau `/var/www/inventory` | Path absolut folder proyek di server |
| `SERVER_PORT` | `22` (opsional) | Port SSH jika bukan 22 |
| `SERVER_PASSWORD`| `Password123` (opsional) | Hanya jika tidak menggunakan SSH Key |

---

### Opsi B: Webhook Deployment (Direkomendasikan untuk cPanel / Shared Hosting)
Sangat cocok jika server Anda tidak mengizinkan SSH port masuk dari luar atau menggunakan hosting berbasis cPanel / Plesk.

#### Konfigurasi GitHub Secrets:
Buka **Settings** -> **Secrets and variables** -> **Actions** -> **New repository secret**:

| Nama Secret | Contoh Nilai | Keterangan |
|-------------|--------------|------------|
| `DEPLOY_WEBHOOK_URL` | `https://domainanda.com/deploy.php` | URL menuju file `deploy.php` di server Anda |
| `DEPLOY_SECRET` | `string_acak_panjang_minimal_32_karakter` | Token keamanan rahasia yang sama dengan file `.env` di server |

*Catatan: Anda juga dapat memasangnya langsung di menu GitHub: **Settings** -> **Webhooks** -> **Add webhook**, masukkan Payload URL `https://domainanda.com/deploy.php`, Secret, dan centang event "Just the push event".*

---

## 3. Langkah Instalasi Awal di Sisi Server (First-Time Setup)

Jalankan perintah ini sekali saja saat pertama kali menyiapkan server production:

### 1. Masuk ke direktori web server dan clone repository:
```bash
cd /var/www/html
git clone https://github.com/anominBlack/inventaris_adiwiyata.git inventory
cd inventory
```

### 2. Atur file Environment (`.env`):
```bash
cp .env.example .env
nano .env
```
Sesuaikan parameter database dan buat token acak untuk `DEPLOY_SECRET`:
```ini
APP_ENV=production
DB_HOST=127.0.0.1
DB_NAME=inventory
DB_USER=inventory_user
DB_PASS=password_database_anda
DEPLOY_SECRET=bikin_kombinasi_token_rahasia_anda_disini_32_char
DEPLOY_BRANCH=main
```

### 3. Import Database:
```bash
mysql -u inventory_user -p inventory < database.sql
```

### 4. Berikan Izin Folder untuk Web Server (Nginx / Apache):
```bash
# Berikan izin eksekusi script deploy
chmod +x deploy.sh

# Atur kepemilikan direktori ke user web server (misal www-data di Ubuntu/Debian)
chown -R www-data:www-data /var/www/html/inventory

# Tandai safe directory pada git agar user www-data tidak diblokir
git config --global --add safe.directory /var/www/html/inventory
```

---

## 4. Cara Kerja Skrip Deployment Otomatis

### A. Skrip Bash Server: `deploy.sh`
File [`deploy.sh`](../deploy.sh) bertugas mengeksekusi tahapan sinkronisasi di server:
1. Menyelaraskan branch `main` dengan remote (`git fetch origin main && git reset --hard origin/main`).
2. Mengembalikan permissions direktori ke `755` dan file ke `644`.
3. Memastikan file `.env` tersedia.
4. Menjalankan PHP syntax health check untuk memastikan tidak ada file PHP yang rusak setelah sinkronisasi.

### B. Skrip Webhook Handler: `deploy.php`
File [`deploy.php`](../deploy.php) bertindak sebagai gerbang webhook yang aman:
- **Validasi Keamanan HMAC SHA-256**: Memverifikasi signature `X-Hub-Signature-256` yang dikirimkan oleh GitHub Actions / Webhook.
- **Validasi Header / Token**: Mencegah akses publik yang tidak memiliki token `DEPLOY_SECRET`.
- **Concurrency Guard (Locking)**: Menggunakan file lock untuk mencegah proses deploy ganda yang bersamaan.
- **Audit Logging**: Mencatat setiap aktivitas deploy, status berhasil/gagal, durasi, dan IP pemanggil ke file `deploy.log`.

---

## 5. Verifikasi & Pemantauan

1. **Memantau Status Build**:
   - Buka tab **Actions** di repositori GitHub Anda.
   - Setiap kali Anda melakukan `git push origin main`, alur pipeline akan muncul dengan icon checklist hijau (berhasil) atau silang merah (gagal).
2. **Melihat Log Deploy di Server**:
   - Anda dapat memeriksa histori pembaruan di server dengan menjalankan:
     ```bash
     cat /var/www/html/inventory/deploy.log
     ```
3. **Menguji Webhook Secara Manual (Testing Endpoint)**:
   ```bash
   curl -i -X POST "https://domainanda.com/deploy.php?secret=TOKEN_RAHASIA_ANDA"
   ```
   Server akan mengembalikan HTTP 200 OK dengan detail commit terbaru dalam format JSON.

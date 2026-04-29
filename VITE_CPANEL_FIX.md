# Mengatasi Masalah CSS/Assets Vite di cPanel (Shared Hosting)

Masalah "CSS ga kebuild" saat deploy ke cPanel biasanya terjadi karena server cPanel (shared hosting) **tidak memiliki Node.js/NPM** untuk menjalankan perintah `npm run build`.

Solusinya adalah melakukan **Build secara Lokal** dan meng-upload (push) hasil build tersebut ke server.

## Langkah Perbaikan

### 1. Build Assets di Komputer Lokal
Jalankan perintah berikut di terminal VS Code komputer anda:

```bash
npm run build
```

Pastikan folder `public/build` terisi dengan file baru (ada folder `assets` dan file `manifest.json`).

### 2. Pastikan Folder Build Ter-commit
Secara default, `public/build` sering diabaikan (ignored). Kita harus memastikannya masuk ke Git.

```bash
# Tambahkan folder build secara paksa jika perlu
git add public/build -f

# Commit perubahan
git commit -m "Fix: Commit built assets for production"

# Push ke repository
git push origin main
```

### 3. Update di cPanel
Masuk ke cPanel (via SSH atau Git Version Control) dan tarik perubahan terbaru:

```bash
git pull origin main
```

### 4. Konfigurasi Penting di Server (cPanel)

Agar assets terbaca dengan benar, periksa hal berikut di server:

#### A. Cek File `.env`
Pastikan `APP_URL` di file `.env` server sesuai dengan domain anda (gunakan `https`).

```env
APP_URL=https://nama-domain-anda.com
APP_ENV=production
APP_DEBUG=false
```

#### B. Hapus File `hot` (Jika Ada)
Cek folder `public`. Jika ada file bernama `hot`, **HAPUS** file tersebut. Keberadaan file ini membuat Laravel mencoba mengakses server development (localhost), bukan file production.

```bash
rm public/hot
```

#### C. Bersihkan Cache
Jalankan perintah ini di terminal server (atau via route khusus jika tidak ada akses SSH) untuk memastikan konfigurasi diperbarui:

```bash
php artisan optimize:clear
php artisan view:clear
```

## Ringkasan
Alur deployment untuk Shared Hosting/cPanel dengan Vite adalah:
1.  **Code** (Edit code)
2.  **Build** (Jalankan `npm run build` di LOKAL)
3.  **Commit** (Simpan hasil build ke Git)
4.  **Pull** (Tarik data di server)

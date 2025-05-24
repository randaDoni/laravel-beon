# Project Laravel & React

Project ini terdiri dari backend Laravel dan frontend React.

**Setup Backend Laravel:**  
1. Clone repo dan masuk folder backend:  
   `git clone https://github.com/username/repo.git && cd repo/backend`  
2. Install dependencies: `composer install`  
3. Copy `.env` dari `.env.example` dan sesuaikan konfigurasi database  
4. Generate app key: `php artisan key:generate`  
5. Jalankan migrasi: `php artisan migrate --seed` (jika ada seeder)  
6. Jalankan server Laravel: `php artisan serve`

**Setup Frontend React:**  
1. Masuk folder frontend: `cd ../frontend`  
2. Install dependencies: `npm install` atau `yarn install`  
3. Jalankan development server: `npm start` atau `yarn start`

> Pastikan file `.env`, `node_modules`, dan `vendor` tidak ikut di-push ke GitHub dengan menggunakan `.gitignore`.  
> Sesuaikan URL API React agar terhubung dengan backend Laravel.

---

Jika ada pertanyaan, silakan hubungi [email@example.com].

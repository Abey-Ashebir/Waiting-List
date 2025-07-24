# 📝 TinaMart Waiting List API

A secure and simple Laravel REST API to manage and analyze early user signups for **TinaMart**.  
This project includes CRUD features, statistics generation, CSV export, weekly reports, and token-based authentication using Laravel Sanctum.

---

## 🚀 Features

- ✅ Add, update, view, and delete waiting list signups
- 🔍 Filter signups by source or date
- 📊 Get statistics: total signups, signup trends, source-based counts
- 📁 Export statistics as downloadable CSV
- 📧 Weekly summary report sent to admin via email
- 🔐 API protected with **Laravel Sanctum** token-based authentication

---

## ⚙️ How to Run the Project Locally

### 1. Clone the Repository

```bash
https://github.com/Abey-Ashebir/Waiting-List.git
cd Tena_Mart
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
```

Update `.env` with your database and mail settings.

### 4. Generate App Key

```bash
php artisan key:generate
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Install Laravel Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 7. Start the Local Server

```bash
php artisan serve
```

API will be available at: `http://localhost:8000`

---

## 🔐 Authentication

Before accessing any route, you must get an access token.

### First create admin user with valid email and password

php artisan tinker

\App\Models\User::create([
'name' => 'Admin',
'email' => 'admin1@tenamart.com',
'password' => bcrypt('yoursecurepassword'),
]);

### then run below command on tinker

$user = \App\Models\User::where('email', 'admin1@tenamart.com')->first();
$token = $user->createToken('api-token')->plainTextToken;
echo $token;

Use this token in **Authorization header** for all requests:

```
Authorization: Bearer your_api_token_here
```

---

## 🧪 API Endpoints

All endpoints below are protected by Sanctum middleware.

| Method | Endpoint                   | Description                     |
| ------ | -------------------------- | ------------------------------- |
| GET    | `/api/waiting-list`        | List signups (supports filters) |
| POST   | `/api/waiting-list`        | Add new signup                  |
| PUT    | `/api/waiting-list/{id}`   | Update signup by ID             |
| DELETE | `/api/waiting-list/{id}`   | Delete signup by ID             |
| GET    | `/api/waiting-list/stats`  | Get statistics                  |
| GET    | `/api/waiting-list/export` | Export CSV report               |

### 🔍 Filters for Listing

- `/api/waiting-list?source=referral`
- `/api/waiting-list?date=2025-07-01`

---

## 📊 Weekly Report (Command)

Custom Laravel Artisan command to send weekly signup stats.

### Run Manually To See the Report , It will generate automatically every week

```bash
php artisan report:weekly
```

### Schedule Weekly Report

In `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
    {
        $schedule->command('report:weekly')
                ->weeklyOn(7, '8:00')
                ->timezone('Africa/Addis_Ababa');
    }
```

---

## 🧠 Project Logic Highlights

- **Controller:** Clean RESTful logic (store, update, destroy, index, stats, export)
- **Command:** Sends total, weekly, and source-based with their corresponding **PERCENTAGE:** stats to admin email
- **CSV Export:** Streams file on download; includes signups per day , Percentage and source
- **Authentication:** Laravel Sanctum for secure access to all endpoints

---

## 📁 `.env.example` Sample

```env
APP_NAME=TenaMart
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@tenamart.com
MAIL_FROM_NAME="Tenamart Waiting List"
```

---

## 📬 Developer

**Abey Ashebir**  
📧 abeyashebir@gmail.com
📧 abayashebirabay@gmail.com

---

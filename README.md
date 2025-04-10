# ATS Demo App

This is a lightweight applicant tracking system built with **Laravel 11**, **Vue 3**, and **TailwindCSS**, designed to demonstrate integration with the [Amiqus API](https://developers.amiqus.co/).

It allows:
- Creating applicants and managing their recruitment status.
- Initiating background checks via Amiqus.
- Viewing and copying perform URLs to send to the user.
- Refreshing the status of checks via Amiqus API.

---

## 🚀 Installation

### 1. Clone the repository

```bash
git clone https://github.com/iamtommetcalfe/ats-demo-app.git
cd ats-demo-app
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install JavaScript dependencies

```bash
npm install
```

### 4. Configure environment variables

Copy `.env.example` to `.env` and set the following keys:

```env
APP_URL=http://localhost:8000

AMIQUS_CLIENT_ID=your_amiqus_client_id
AMIQUS_CLIENT_SECRET=your_amiqus_client_secret
AMIQUS_REDIRECT_URI=http://localhost:8000/amiqus/callback
```

If running locally, you can use [ngrok](https://ngrok.com/) or similar to expose `localhost` securely for testing OAuth.

### 5. Generate app key

```bash
php artisan key:generate
```

### 6. Set up your database

Make sure your DB credentials are configured in `.env`, then run:

```bash
php artisan migrate --seed
```

This will create the necessary tables and seed 50 example applicants.

### 7. Compile front-end assets

```bash
npm run dev
```

(Or use `npm run build` for production.)

### 8. Run the application

```bash
php artisan serve
```

Then visit [http://localhost:8000](http://localhost:8000) in your browser.

---

## 🔐 Amiqus Integration

To connect your Amiqus sandbox account:

1. Visit the **"Manage Amiqus Connection"** link in the top right corner.
2. Click **"Connect to Amiqus"** to begin the OAuth handshake.
3. Once authorised, tokens will be stored in the database and reused/auto-refreshed when needed.

---

## ✅ Features

- Fully local setup with seeded applicant data
- OAuth2 token handling with refresh support
- Status-based applicant workflow
- Background check triggering
- Perform URL capture & copy
- Real-time status refresh from Amiqus
- Clean and responsive UI with TailwindCSS

---

## 🧪 Testing the Flow

1. Navigate to the homepage
2. Click on an applicant
3. Change their status to **"background check"** and confirm
4. Once redirected and authenticated, the background check will be initiated
5. Copy the perform URL and simulate sharing it with the applicant
6. Click "Update" next to a background check to refresh its status from Amiqus

---

## 🧹 Useful Commands

- Reset database:  
  ```bash
  php artisan migrate:fresh --seed
  ```

- Clear config cache:  
  ```bash
  php artisan config:clear
  ```

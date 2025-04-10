# ATS Demo App

This is a lightweight applicant tracking system built with **Laravel 11**, **Vue 3**, and **TailwindCSS**, designed to demonstrate integration with the [Amiqus API](https://developers.amiqus.co/).

It allows:
- Creating applicants and managing their recruitment status.
- Initiating background checks via Amiqus.
- Viewing and copying perform URLs for the end users.
- Refreshing the status of checks via Amiqus API.

---

## 🚀 Docker Installation (Recommended)

### 1. Clone the repository

```bash
git clone https://github.com/iamtommetcalfe/ats-demo-app.git
cd ats-demo-app
```

### 2. Set up environment variables

Copy `.env.example` to `.env` and update the following:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=amiqus
DB_USERNAME=amiqus
DB_PASSWORD=secret

AMIQUS_CLIENT_ID=your_amiqus_client_id
AMIQUS_CLIENT_SECRET=your_amiqus_client_secret
AMIQUS_REDIRECT_URI=http://localhost:8000/amiqus/callback
```

If running locally, consider using [ngrok](https://ngrok.com/) to expose `localhost` securely for OAuth testing.

---

## 📦 Start the Application

You can either run everything **manually** or via the provided **Makefile**.

---

### 🔧 Option A: Manual Docker Setup

```bash
# Build and start containers
docker compose up --build -d

# Install dependencies inside container
docker compose exec app composer install
docker compose exec app npm install

# Generate app key
docker compose exec app php artisan key:generate

# Migrate and seed the database
docker compose exec app php artisan migrate --seed

# Start Vite dev server (run this in a second terminal)
docker compose exec app npm run dev
```

---

### ✅ Option B: Use Makefile

Run everything with a single command:

```bash
make setup
```

Then in a second terminal:

```bash
make dev
```

---

### Makefile Commands

```make
make setup     # Build, up, install deps, migrate, seed
make dev       # Start the Vite dev server (hot reload)
make migrate   # Run database migrations
make seed      # Run DB seeders
make fresh     # Reset DB and reseed
```

---

## ✅ Access the App

- Laravel app: [http://localhost:8000](http://localhost:8000)
- Vite dev server: automatically injected via Blade (hot reload)
- Amiqus callback URL: [http://localhost:8000/amiqus/callback](http://localhost:8000/amiqus/callback)

---

## 🔐 Amiqus Integration

To connect your Amiqus sandbox account:

1. Click **"Manage Amiqus Connection"** at the top of the app
2. Authorise the connection to store your OAuth token
3. Tokens will be persisted and automatically refreshed when needed

---

## 🧪 Testing the Flow

1. Visit the homepage and view an applicant
2. Change their status to **"background check"** and confirm
3. Authorise with Amiqus
4. A record is created, and the status updates to **"background check in progress"**
5. Copy or send the perform URL to a test email
6. Use the **"Update"** button to check status from Amiqus live

---

## 🧹 Useful Extras

- Reset database:  
  ```bash
  docker compose exec app php artisan migrate:fresh --seed
  ```

- Clear cache:  
  ```bash
  docker compose exec app php artisan config:clear
  ```

---

## 🧑‍💻 Tech Stack

- Laravel 11
- Vue 3 + Vite + TailwindCSS
- MySQL 8 (Docker)
- OAuth2 (Amiqus)
- Custom Docker Compose setup

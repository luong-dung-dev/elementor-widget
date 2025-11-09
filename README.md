## WordPress (Docker) – Quick Start

Requirement: Install Docker Desktop on macOS.

### 1) Start services

```bash
cd /Users/dung.luong/wordpress_project
# Create .env from sample (if not exists)
cp env.sample .env
docker compose up -d
```

- WordPress: `http://localhost:8080`
- phpMyAdmin: `http://localhost:8081` (Server: `db`, User: `${MYSQL_USER}`, Pass: `${MYSQL_PASSWORD}`)

### 2) Install WordPress via browser

Open `http://localhost:8080` and complete the setup wizard.

You can customize variables like URL/site/user/password in `.env`.

### 3) Working directory structure

- `wp-content/themes` – where themes live
- `wp-content/plugins` – where plugins live
- `wp-content/uploads` – media uploads

WordPress core and DB data are persisted by Docker volumes (`wordpress_data`, `db_data`).

### 4) Useful commands

```bash
# Stop services
docker compose down

# Tail WordPress logs
docker compose logs -f wordpress

# Run WP-CLI interactively
docker compose run --rm wpcli wp plugin list
```

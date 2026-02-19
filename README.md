# DCIM Revival Project

Docker-based development environment for the VIODCIM project.

## Quick Start

1. **Start the environment:**
   ```bash
   docker-compose up -d
   ```

2. **Access the application:**
   - DCIM App: http://localhost:8080
   - PHPMyAdmin: http://localhost:8081

3. **Database credentials:**
   - Host: `db`
   - Database: `dcim`
   - User: `dcim`
   - Password: `dcim_secret`
   - Root Password: `root_secret`

## Services

| Service | Port | Description |
|---------|------|-------------|
| web (Nginx) | 8080 | Web server |
| app (PHP) | 9000 | PHP-FPM |
| db (MySQL) | 3306 | Database |
| phpmyadmin | 8081 | Database admin |

## Development Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Access PHP container
docker-compose exec app bash

# Access database
docker-compose exec db mysql -u dcim -p dcim

# Rebuild after Dockerfile changes
docker-compose up -d --build
```

## Project Structure

```
dcim-revival/
├── docker-compose.yml      # Service orchestration
├── Dockerfile              # PHP container build
├── src/                    # Application code (from original project)
├── docker/
│   ├── nginx/
│   │   └── default.conf    # Nginx configuration
│   ├── php/
│   │   └── php.ini         # PHP configuration
│   └── mysql/
│       └── init/
│           └── 01-schema.sql  # Database schema
└── README.md               # This file
```

## Configuration

The database configuration in `src/db.inc.php` needs to be updated to use environment variables:

```php
$dbhost = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'dcim';
$dbuser = getenv('DB_USER') ?: 'dcim';
$dbpass = getenv('DB_PASS') ?: 'dcim_secret';
```

## Security Notes

- Default credentials are for development only
- Change all passwords before production deployment
- Enable HTTPS in production
- Review and update `db.inc.php` security settings

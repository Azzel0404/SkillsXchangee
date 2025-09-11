# SkillsXchangee Deployment Guide

This guide covers deploying your Laravel application to Railway and Render platforms.

## Prerequisites

- Git repository with your code
- GitHub account (for connecting to deployment platforms)
- Database credentials (MySQL)

## Railway Deployment

### 1. Connect to Railway

1. Go to [Railway.app](https://railway.app)
2. Sign up/Login with GitHub
3. Click "New Project" → "Deploy from GitHub repo"
4. Select your SkillsXchangee repository

### 2. Add Database

1. In your Railway project, click "New" → "Database" → "MySQL"
2. Railway will automatically create a MySQL database
3. Note down the connection details from the database service

### 3. Configure Environment Variables

In your Railway project settings, add these environment variables:

```
APP_NAME=SkillsXchangee
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.railway.app

# Database (Railway will provide these automatically)
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}

# Logging
LOG_CHANNEL=stderr
LOG_LEVEL=error

# Cache and Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 4. Deploy

Railway will automatically deploy when you push to your main branch. The deployment process includes:

- Building the Docker container
- Installing PHP dependencies
- Installing Node.js dependencies
- Building frontend assets
- Running database migrations
- Starting the application

## Render Deployment

### 1. Connect to Render

1. Go to [Render.com](https://render.com)
2. Sign up/Login with GitHub
3. Click "New" → "Web Service"
4. Connect your GitHub repository

### 2. Configure Web Service

**Basic Settings:**
- **Name**: skillsxchangee
- **Environment**: PHP
- **Plan**: Free
- **Build Command**: 
  ```bash
  composer install --no-dev --optimize-autoloader --no-interaction
  npm install
  npm run build
  php artisan key:generate --force --no-interaction
  php artisan config:cache --no-interaction
  php artisan route:cache --no-interaction
  php artisan view:cache --no-interaction
  ```
- **Start Command**: `php -S 0.0.0.0:$PORT -t public`
- **Health Check Path**: `/health`

### 3. Add Database

1. In Render dashboard, click "New" → "PostgreSQL" (or MySQL if available)
2. Choose "Free" plan
3. Note down the connection details

### 4. Configure Environment Variables

In your Render web service settings, add these environment variables:

```
APP_NAME=SkillsXchangee
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Logging
LOG_CHANNEL=stderr
LOG_LEVEL=error

# Cache and Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 5. Deploy

Render will automatically deploy when you push to your main branch.

## Environment Variables Reference

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_NAME` | Application name | SkillsXchangee |
| `APP_ENV` | Environment | production |
| `APP_DEBUG` | Debug mode | false |
| `APP_URL` | Application URL | https://your-app.railway.app |
| `DB_HOST` | Database host | localhost |
| `DB_DATABASE` | Database name | skillsxchangee |
| `DB_USERNAME` | Database username | root |
| `DB_PASSWORD` | Database password | your-password |

### Optional Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `LOG_CHANNEL` | Log channel | stderr |
| `LOG_LEVEL` | Log level | error |
| `CACHE_DRIVER` | Cache driver | file |
| `SESSION_DRIVER` | Session driver | file |
| `QUEUE_CONNECTION` | Queue connection | sync |

## Post-Deployment

### 1. Verify Deployment

- Check the health endpoint: `https://your-app-url/health`
- Test the main application: `https://your-app-url/`
- Verify database connection by checking if migrations ran successfully

### 2. Database Seeding

If you have seeders, you may need to run them manually:

```bash
php artisan db:seed --force
```

### 3. SSL/HTTPS

Both Railway and Render provide automatic SSL certificates for custom domains.

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Verify database credentials
   - Check if database service is running
   - Ensure database exists

2. **Application Key Missing**
   - The deployment process should generate this automatically
   - If not, run: `php artisan key:generate --force`

3. **Asset Build Failed**
   - Check Node.js version compatibility
   - Verify all dependencies are installed
   - Check for build errors in logs

4. **Migration Failed**
   - Check database permissions
   - Verify migration files are correct
   - Check for conflicting migrations

### Logs

- **Railway**: Check logs in the Railway dashboard
- **Render**: Check logs in the Render dashboard under "Logs" tab

## File Structure

Your deployment-ready project includes:

```
├── Dockerfile              # Railway deployment
├── Procfile               # Heroku-style deployment
├── railway.json           # Railway configuration
├── render.yaml            # Render configuration
├── start.sh               # Startup script
├── .env                   # Local environment (not deployed)
├── composer.json          # PHP dependencies
├── package.json           # Node.js dependencies
└── DEPLOYMENT.md          # This guide
```

## Support

For platform-specific issues:
- **Railway**: [Railway Documentation](https://docs.railway.app)
- **Render**: [Render Documentation](https://render.com/docs)

#!/bin/sh

# Create log directories if they don't exist
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Set up Laravel environment
cd /var/www/html

# Ensure .env file exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Check if APP_KEY exists and is not empty/placeholder
# Priority: 1) .env file in container, 2) Environment variable, 3) Generate new
APP_KEY_VALUE=$(grep "^APP_KEY=" .env 2>/dev/null | cut -d '=' -f2- | xargs || echo "")

if [ -n "$APP_KEY_VALUE" ] && [ "$APP_KEY_VALUE" != "" ] && [ "$APP_KEY_VALUE" != "YOUR_APP_KEY_HERE" ] && echo "$APP_KEY_VALUE" | grep -q "^base64:"; then
    # APP_KEY in .env file is valid, use it
    echo "APP_KEY found in .env file and is valid"
elif [ -n "$APP_KEY" ] && [ "$APP_KEY" != "" ] && echo "$APP_KEY" | grep -q "^base64:"; then
    # APP_KEY from environment variable is valid, update .env file
    echo "Using APP_KEY from environment variable..."
    if grep -q "^APP_KEY=" .env 2>/dev/null; then
        sed -i.bak "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env && rm -f .env.bak 2>/dev/null || true
    else
        echo "APP_KEY=$APP_KEY" >> .env
    fi
else
    # No valid APP_KEY found, generate new one
    echo "APP_KEY not found or invalid, generating new key..."
    php artisan key:generate --force
    # Read the generated key
    GENERATED_KEY=$(grep "^APP_KEY=" .env 2>/dev/null | cut -d '=' -f2- | xargs || echo "")
    if [ -n "$GENERATED_KEY" ]; then
        echo "Generated APP_KEY: ${GENERATED_KEY:0:20}..."
    fi
fi

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Check if queue table exists and run migrations if needed
# Uncomment if you want to run migrations automatically
# php artisan migrate --force

# Create a symbolic link for storage
php artisan storage:link

# Optimize Laravel
php artisan optimize

# Check queue connection
echo "Checking queue configuration..."
php artisan queue:restart

# Start all services using Supervisor
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf

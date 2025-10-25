#!/bin/bash
set -e

echo "Starting Laravel application initialization..."

# Get PORT from environment, default to 8000
export PORT=${PORT:-8000}
echo "Configuring Nginx to listen on port $PORT"

# Replace PORT_PLACEHOLDER with actual port number
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/nginx.conf

# Verify the substitution worked
echo "Nginx configuration after substitution:"
grep "listen" /etc/nginx/nginx.conf | head -3

# Test nginx configuration
nginx -t

# Initialize storage directory if needed
if [ ! -d "/var/www/storage/app" ]; then
    echo "Initializing storage directory..."
    cp -r /var/www/storage-init/* /var/www/storage/
fi

# Ensure proper permissions for storage and cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

echo "Running Laravel optimization commands..."

# Clear and cache configurations (as www-data user)
su -s /bin/bash www-data -c "php artisan config:cache"
su -s /bin/bash www-data -c "php artisan route:cache"
su -s /bin/bash www-data -c "php artisan view:cache"

echo "Laravel initialization complete!"

# Execute the CMD from Dockerfile (supervisord)
exec "$@"

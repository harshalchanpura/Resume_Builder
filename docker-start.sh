#!/bin/sh
set -e
PORT="${PORT:-80}"
# Ensure exactly one MPM is loaded (fixes AH00534 "More than one MPM loaded")
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.load ]; then
  ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
  ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
fi
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
# Make sure the uploads folder (or mounted Railway volume) is writable
mkdir -p /var/www/html/uploads
chown -R www-data:www-data /var/www/html/uploads
exec apache2-foreground

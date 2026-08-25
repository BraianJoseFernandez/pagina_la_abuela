#!/usr/bin/env bash
set -e

echo "=== Configurando VirtualHost de Apache con SSL para Rotisería La Abuela ==="

# 1. Copiar certificados SSL a /etc/httpd/conf/ssl/
echo "1. Instalando certificados SSL generados con mkcert..."
sudo cp /var/www/html/pagina_la_abuela/apache/ssl/menu-rotilaabuela.test.pem /etc/httpd/conf/ssl/menu-rotilaabuela.test.pem
sudo cp /var/www/html/pagina_la_abuela/apache/ssl/menu-rotilaabuela.test-key.pem /etc/httpd/conf/ssl/menu-rotilaabuela.test-key.pem
sudo cp /var/www/html/pagina_la_abuela/apache/ssl/menu-rotilaabuela.test.pem /etc/httpd/conf/ssl/pagina_la_abuela.test.pem
sudo cp /var/www/html/pagina_la_abuela/apache/ssl/menu-rotilaabuela.test-key.pem /etc/httpd/conf/ssl/pagina_la_abuela.test-key.pem

# 2. Instalar archivo de configuración en /etc/httpd/conf/vhosts/
echo "2. Copiando VirtualHost a /etc/httpd/conf/vhosts/menu-rotilaabuela.test.conf..."
sudo cp /var/www/html/pagina_la_abuela/apache/menu-rotilaabuela.test.conf /etc/httpd/conf/vhosts/menu-rotilaabuela.test.conf

# 3. Asegurar entrada en /etc/hosts
echo "3. Verificando /etc/hosts..."
if ! grep -q "menu-rotilaabuela.test" /etc/hosts; then
    echo "127.0.0.1 menu-rotilaabuela.test" | sudo tee -a /etc/hosts
    echo "Dominio menu-rotilaabuela.test añadido a /etc/hosts."
else
    echo "Dominio menu-rotilaabuela.test ya existe en /etc/hosts."
fi

# 4. Asegurar permisos de almacenamiento y subidas
echo "4. Ajustando permisos de carpetas storage y cache..."
chmod -R 777 /var/www/html/pagina_la_abuela/storage /var/www/html/pagina_la_abuela/bootstrap/cache /var/www/html/pagina_la_abuela/public/imagenes/uploads

# 5. Reiniciar Apache
echo "5. Reiniciando Apache (httpd.service)..."
sudo systemctl restart httpd

echo "✅ ¡Configuración completada con éxito!"
echo "Puedes ingresar a:"
echo "👉 https://menu-rotilaabuela.test"
echo "👉 https://pagina_la_abuela.test"

# 🧠 Tots Event Booking - API

Este proyecto es una API construida con Symfony 6 que permite el registro de usuarios, autenticación JWT, gestión de espacios y reservas.

## 🔧 Requisitos

-   PHP 8.2
-   Composer
-   PostgreSQL
-   Symfony CLI (opcional)
-   AWS CLI y EB CLI (para despliegue)

## 🛠️ Instalación local

```bash
git clone https://github.com/AlexBarciaJS/ReservaAPI.git
cd api

# Instalar dependencias
composer install

# Copiar variables de entorno
cp .env .env.local

# Edita DATABASE_URL con tus credenciales locales de PostgreSQL
# y configura el secreto JWT si usas LexikJWTAuthenticationBundle

# Crear base de datos y migraciones
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Iniciar el servidor
symfony server:start
```

🧪 Ejecutar tests
php bin/phpunit

Swagger
http://localhost:8000/api/doc/ui

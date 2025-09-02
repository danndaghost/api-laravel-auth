# Laravel Auth Microservice Skeleton

Un microservicio de autenticación completo construido con Laravel 12, que incluye OAuth2 con Laravel Passport y gestión de roles/permisos con Spatie Laravel Permission.

## 🚀 Características

- **Autenticación OAuth2** con Laravel Passport
- **Sistema de Roles y Permisos** con Spatie Laravel Permission
- **API RESTful** completamente documentada
- **Docker Ready** con configuración incluida
- **Laravel 12** con PHP 8.2
- **Middleware de autenticación** para proteger rutas
- **Gestión completa de usuarios**

## 📋 Requisitos

- PHP >= 8.2
- Composer
- MySQL 8.0
- Redis (opcional, para caché)
- Docker y Docker Compose (opcional)

## 🛠️ Tecnologías

- **Framework**: Laravel 12.0
- **PHP**: 8.2
- **Autenticación**: Laravel Passport 12.0
- **Roles/Permisos**: Spatie Laravel Permission 6.0
- **Base de datos**: MySQL 8.0
- **Cache**: Redis

## 📦 Instalación

### Opción 1: Instalación tradicional

1. Clonar el repositorio:
```bash
git clone https://github.com/tu-usuario/tracking-auth.git
cd tracking-auth
```

2. Instalar dependencias:
```bash
composer install
```

3. Copiar archivo de configuración:
```bash
cp .env.example .env
```

4. Generar clave de aplicación:
```bash
php artisan key:generate
```

5. Configurar base de datos en `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_auth
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

6. Ejecutar migraciones:
```bash
php artisan migrate
```

7. Instalar Laravel Passport:
```bash
php artisan passport:install
```

8. Crear roles y permisos iniciales:
```bash
php artisan db:seed
```

9. Iniciar servidor de desarrollo:
```bash
php artisan serve
```

### Opción 2: Instalación con Docker

1. Clonar el repositorio:
```bash
git clone https://github.com/tu-usuario/tracking-auth.git
cd tracking-auth
```

2. Copiar archivo de configuración:
```bash
cp .env.example .env
```

3. Construir y ejecutar contenedores:
```bash
docker-compose up -d --build
```

4. Ejecutar migraciones dentro del contenedor:
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan passport:install
docker-compose exec app php artisan db:seed
```

La aplicación estará disponible en:
- **API**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

## 📚 Documentación de API

### Base URL
```
http://localhost:8080/api
```

### Autenticación

#### Registro de usuario
```http
POST /auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "Usuario registrado correctamente",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-01-09T12:00:00.000000Z"
    }
}
```

#### Login
```http
POST /auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Respuesta exitosa (200):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "token_type": "Bearer"
}
```

#### Obtener información del usuario autenticado
```http
GET /auth/me
Authorization: Bearer {access_token}
```

**Respuesta exitosa (200):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "roles": ["viewer"],
    "permissions": ["view reports"]
}
```

#### Logout
```http
POST /auth/logout
Authorization: Bearer {access_token}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Sesión cerrada correctamente"
}
```

### Gestión de Roles (Solo Admin)

#### Listar roles
```http
GET /roles
Authorization: Bearer {admin_access_token}
```

#### Crear rol
```http
POST /roles
Authorization: Bearer {admin_access_token}
Content-Type: application/json

{
    "name": "editor",
    "guard_name": "api"
}
```

#### Asignar permisos a un rol
```http
POST /roles/{role}/permissions
Authorization: Bearer {admin_access_token}
Content-Type: application/json

{
    "permissions": ["edit articles", "publish articles"]
}
```

### Gestión de Permisos (Solo Admin)

#### Listar permisos
```http
GET /permissions
Authorization: Bearer {admin_access_token}
```

#### Crear permiso
```http
POST /permissions
Authorization: Bearer {admin_access_token}
Content-Type: application/json

{
    "name": "delete articles",
    "guard_name": "api"
}
```

### Gestión de Usuarios (Solo Admin)

#### Listar usuarios
```http
GET /users
Authorization: Bearer {admin_access_token}
```

#### Crear usuario
```http
POST /users
Authorization: Bearer {admin_access_token}
Content-Type: application/json

{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123"
}
```

#### Asignar roles a un usuario
```http
POST /users/{user}/roles
Authorization: Bearer {admin_access_token}
Content-Type: application/json

{
    "roles": ["editor", "moderator"]
}
```

#### Asignar permisos directos a un usuario
```http
POST /users/{user}/permissions
Authorization: Bearer {admin_access_token}
Content-Type: application/json

{
    "permissions": ["special permission"]
}
```

### Rutas de ejemplo con permisos

#### Dashboard de Admin (requiere rol admin)
```http
GET /admin/dashboard
Authorization: Bearer {admin_access_token}
```

#### Ver reportes (requiere permiso 'view reports')
```http
GET /reports/view
Authorization: Bearer {access_token}
```

#### Editar artículos (requiere permiso 'edit articles')
```http
GET /articles/edit
Authorization: Bearer {access_token}
```

## 🔒 Roles y Permisos por defecto

### Roles
- **admin**: Acceso completo al sistema
- **editor**: Puede editar y publicar contenido
- **viewer**: Solo puede ver contenido (rol por defecto para nuevos usuarios)

### Permisos
- `view reports`: Ver reportes del sistema
- `edit articles`: Editar artículos
- `publish articles`: Publicar artículos
- `manage users`: Gestionar usuarios
- `manage roles`: Gestionar roles y permisos

## 🐳 Docker

### Servicios incluidos

- **app**: Aplicación Laravel (PHP 8.2 + Nginx)
- **db**: MySQL 8.0
- **redis**: Redis para caché
- **phpmyadmin**: Interfaz web para MySQL

### Comandos útiles de Docker

```bash
# Ver logs
docker-compose logs -f app

# Ejecutar comandos artisan
docker-compose exec app php artisan [comando]

# Acceder al contenedor
docker-compose exec app bash

# Detener servicios
docker-compose down

# Detener y eliminar volúmenes
docker-compose down -v
```

## 🧪 Testing

Ejecutar tests:
```bash
php artisan test
# o con Docker
docker-compose exec app php artisan test
```

## 🤝 Contribuir

1. Fork el proyecto
2. Crea tu rama de características (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👥 Autor

- **Tu Nombre** - *Trabajo inicial* - [tu-usuario](https://github.com/tu-usuario)

## 🙏 Agradecimientos

- Laravel Team por el excelente framework
- Spatie por el paquete de roles y permisos
- La comunidad de Laravel por su apoyo continuo

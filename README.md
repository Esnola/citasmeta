# Citas Dentista

Aplicación Laravel para gestionar citas, pacientes y recordatorios por WhatsApp desde una sola interfaz.

## Qué incluye

- Panel principal con métricas y próximos envíos
- Gestión de pacientes y citas
- Programación de mensajes de WhatsApp
- Importación de datos desde Excel
- Plantillas reutilizables para mensajes
- Envío manual y envío programado
- Pruebas automáticas para las partes principales del flujo

## Stack

- Laravel 13
- Livewire 4
- Flux UI
- Tailwind CSS 4
- PHPUnit 12

## Requisitos

- PHP 8.4
- Composer
- Node.js y npm
- Base de datos compatible con Laravel

## Instalación

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configura después la base de datos y el resto de variables en `.env`.

## Arranque local

```bash
php artisan migrate
php artisan serve
npm run dev
```

Si prefieres un arranque completo del entorno:

```bash
composer run dev
```

## Pruebas

```bash
php artisan test --compact
```

Para una prueba concreta:

```bash
php artisan test --compact --filter=NombreDelTest
```

## Flujo principal

1. Crear o importar pacientes
2. Registrar citas o mensajes
3. Elegir una plantilla
4. Programar el envío
5. Ejecutar el comando de mensajes pendientes

## WhatsApp

La app soporta distintos drivers de envío mediante configuración. Revisa `config/whatsapp.php` y las credenciales asociadas en `.env` para dejar activo el canal que uses.

## Estructura útil

- `app/Livewire/`: componentes interactivos
- `app/Models/`: modelos de dominio
- `app/Services/WhatsApp/`: lógica de envío
- `database/migrations/`: esquema de la base de datos
- `tests/Feature/`: pruebas funcionales del flujo

## Notas

- El proyecto ya incluye skills y guías para seguir el trabajo desde otra sesión o equipo.
- Si cambias frontend, recuerda ejecutar `npm run dev` o `npm run build`.

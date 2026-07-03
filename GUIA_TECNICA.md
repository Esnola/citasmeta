# Guía técnica de la aplicación

## 1. Qué hace esta app

Esta aplicación sirve para gestionar:

- Clientes
- Citas
- Mensajes de WhatsApp
- Importación de datos desde Excel
- Plantillas de mensajes
- Configuración de recordatorios y conexión con WhatsApp
- Administración de usuarios y seguridad

El flujo principal es:

1. Crear o importar clientes.
2. Registrar citas o programar mensajes.
3. Elegir una plantilla.
4. Enviar ahora o dejar el envío programado.
5. Sincronizar el estado de entrega cuando llegue la respuesta del proveedor.

## 2. Requisitos

- PHP 8.4
- Composer
- Node.js y npm
- Base de datos compatible con Laravel

## 3. Arranque local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
npm run dev
```

Si prefieres arrancar todo junto:

```bash
composer run dev
```

## 4. Acceso y navegación

Las pantallas principales están en el menú lateral:

- Dashboard
- Clientes
- Citas
- Importar Excel
- Ajustes
- Usuarios
- Seguridad

El acceso a administración está restringido al usuario con `id = 1`.

## 5. Dashboard

El panel principal muestra:

- Mensajes pendientes
- Mensajes enviados
- Mensajes fallidos
- Próximos mensajes programados

## 6. Clientes

En `Clientes` puedes:

- Buscar por nombre, apellidos o teléfono
- Ver la lista paginada
- Crear un cliente nuevo
- Editar un cliente existente
- Eliminar un cliente
- Ir a sus citas
- Programar una cita o un WhatsApp desde su ficha

### Reglas importantes

- El teléfono se normaliza al formato internacional cuando es posible.
- Si creas un cliente con un teléfono ya existente, el sistema reutiliza ese registro.

## 7. Citas

En `Citas` puedes:

- Ver todas las citas o solo las de un cliente concreto
- Filtrar por nombre, apellidos y estado de notificación
- Ordenar por cliente o por fecha
- Crear una cita
- Editar una cita futura que todavía no haya sido enviada
- Marcar una cita como activa o inactiva
- Enviar el WhatsApp inmediatamente
- Eliminar una cita

### Reglas de negocio

- No se pueden programar citas para domingo.
- Las citas pasadas no se pueden enviar.
- Una cita enviada ya no se puede modificar.
- Si desactivas una cita, se eliminan sus mensajes pendientes asociados.

### Estados visibles

- `Enviado`: el WhatsApp ya fue enviado
- `Entregado`: el proveedor confirmó la entrega
- `Leído`: el destinatario abrió el mensaje
- `Pendiente`: sigue en cola para enviarse

## 8. Programar mensajes desde un cliente

En la pantalla de clientes hay un bloque para programar WhatsApp directamente desde una ficha.

### Pasos

1. Busca el cliente.
2. Pulsa `Usar`.
3. Elige plantilla.
4. Selecciona fecha y hora.
5. Pulsa `Programar mensaje` o `Enviar ahora`.

### Nota

La fecha mínima disponible es al día siguiente y no se permite domingo.

## 9. Importar Excel

En `Importar Excel` puedes cargar un archivo y previsualizarlo antes de importar.

### Formatos admitidos

- `.xlsx`
- `.xls`
- `.csv`

### Límite

- Máximo 10 MB

### Flujo

1. Selecciona una plantilla.
2. Sube el archivo.
3. Pulsa `Previsualizar`.
4. Revisa las filas.
5. Si todo está bien, pulsa `Importar`.

### Columnas recomendadas

La importación reconoce varios nombres de columna. Las más útiles son:

- `nombre`
- `apellidos`
- `telefono`
- `fecha` o `scheduled_date`
- `hora` o `scheduled_time`
- `plantilla` opcional

También acepta alias como `fecha_cita`, `dia`, `hora_cita`, `telefono_movil` o `whatsapp_number`.

### Resultado del import

- Crea o actualiza el cliente por teléfono.
- Genera un mensaje pendiente.
- Guarda la referencia de la plantilla usada.

## 10. Plantillas de WhatsApp

En `Ajustes > Plantillas` puedes:

- Crear una plantilla nueva
- Editarla
- Marcarla como predeterminada
- Activarla o desactivarla
- Cambiar su orden
- Eliminarla

### Variables disponibles

Las plantillas pueden usar:

- `[NOMBRE]`
- `[APELLIDOS]`
- `[TELEFONO]`
- `[DIA]`
- `[HORA]`

## 11. Ajustes

La pantalla de ajustes está organizada en bloques movibles y plegables.

### 11.1 Prueba de conexión

Sirve para probar el envío real de WhatsApp.

Puedes:

- Indicar un destinatario
- Ver el payload antes de enviar
- Enviar al destinatario guardado si existe `META_WHATSAPP_TEST_RECIPIENT`

### 11.2 Estado actual

Muestra:

- Driver activo
- Plantilla por defecto
- Estado de credenciales de Cloud API (Meta)
- Phone Number ID configurado
- Token de acceso

### 11.3 Cloud API (Meta)

Variables habituales:

- `WHATSAPP_DRIVER=cloud_api`
- `WHATSAPP_DEFAULT_TEMPLATE=confirmar_cita`
- `META_WHATSAPP_PHONE_NUMBER_ID=TuPhoneNumberID`
- `META_WHATSAPP_ACCESS_TOKEN=TuTokenDeAcceso`

La plantilla de Meta y la clave local de la app deben coincidir. Si en Meta la plantilla se llama `confirmar_cita`, usa ese mismo valor en `WHATSAPP_DEFAULT_TEMPLATE`.

Para desarrollar usa `log` que solo registra los mensajes en el log sin enviar.

### 11.4 Tiempos de envío

Configura preferencias de recordatorios para:

- WhatsApp
- Email

## 12. Usuario administrador

El administrador principal puede:

- Crear usuarios
- Editar usuarios
- Eliminar usuarios, excepto su propia cuenta
- Cambiar su contraseña

## 13. Webhook de estado WhatsApp

La app puede recibir callbacks de estado de WhatsApp Cloud API (Meta).

Ese endpoint:

- Recibe el payload del proveedor
- Sincroniza el estado de entrega y lectura

## 14. Comandos útiles

```bash
php artisan test --compact
php artisan test --compact --filter=NombreDelTest
php artisan migrate
php artisan route:list
```

Si cambias frontend y no ves reflejado el cambio, ejecuta:

```bash
npm run dev
```

o

```bash
npm run build
```

## 15. Problemas habituales

- Si no envía WhatsApp, revisa `WHATSAPP_DRIVER` y las credenciales de Cloud API en `.env`.
- Si el webhook no actualiza estados, comprueba la configuración del proveedor.
- Si la importación no muestra datos, revisa los nombres de columna del Excel.
- Si una cita no deja enviarse, verifica que sea futura y esté activa.

# Guía para retomar el trabajo

Proyecto: `citasmeta`

## Estado actual

- Aplicación Laravel con Livewire para gestionar clientes, citas y envíos WhatsApp.
- El listado de citas y clientes está separado de sus formularios de creacion/edicion.
- La página de mensajes fue eliminada porque no tenía uso.
- Las citas se ordenan por fecha ascendente por defecto.
- Las citas pasadas o ya enviadas no se pueden editar ni cambiar de estado; solo eliminar.
- En ficha de cliente, las citas futuras no enviadas permiten toggle de activo; las pasadas/enviadas muestran acción de
  eliminar.
- El envío automático de WhatsApp está activo mediante el comando `whatsapp:dispatch-due`.
- El scheduler ejecuta ese comando cada minuto en `routes/console.php`.

## Configuración WhatsApp Cloud API (Meta)

El flujo real está preparado en `app/Services/WhatsApp/WhatsAppSender.php`.

Variables esperadas en `.env`:

```env
WHATSAPP_DRIVER=cloud_api
WHATSAPP_CLOUD_API_PHONE_NUMBER_ID=TuPhoneNumberID
WHATSAPP_CLOUD_API_ACCESS_TOKEN=TuTokenDeAcceso
```

Para desarrollo usa `WHATSAPP_DRIVER=log` que solo registra los mensajes sin enviar.

## Comandos utiles

Limpiar caché de configuración después de cambiar `.env`:

```bash
php artisan config:clear --no-interaction
```

```bash
php artisan whatsapp:dispatch-due --no-interaction
```

Formatear PHP antes de cerrar cambios:

```bash
vendor/bin/pint --dirty --format agent
```

## Envío automático realizado

Se ejecuto:

```bash
php artisan whatsapp:dispatch-due --no-interaction
```

Resultado:

```text
Queued 7 appointment message(s).
Processed 1 due message(s).
```

Estado posterior en ese momento:

- Enviados: 1
- Pendientes: 6
- Fallidos: 0

Los 6 pendientes tienen fecha futura y se enviarán cuando llegue su `scheduled_for`.

## Rutas principales

- `/appointments`: listado de citas.
- `/appointments/create`: crear cita.
- `/clients`: listado de clientes.
- `/clients/create`: crear cliente.
- `/settings`: ajustes, plantillas y prueba de conexión WhatsApp.
- `/imports`: importaciones.

## Pendiente recomendado al retomar

1. Preparar la plantilla de correo de WhatsApp.
2. Preparar la plantilla de correo de cita cancelada.
3. Preparar la plantilla de correo de cita reprogramada.
4. Preparar la plantilla de correo de cita confirmada.
5. Preparar la plantilla de correo de cita enviada.
6. Preparar la plantilla de correo de cita rechazada.
7. Preparar la plantilla de correo de cita rechazada por el cliente.
8. Preparar la plantilla de correo de cita rechazada por el dentista.
9. Preparar para enviar correos de recordatorio de cita.
10. Preparar para enviar correos de confirmación de cita.
11. Los envíos son marcados como correctos solo con que tengan el estado queued habrá que marcarlos como en espera y
    chequear el estado de alguna manera mas tarde para actualizar. El estado de activo pasará a false y enviado a true
    en la Base de Datos cuando se verifique la correcta entrega al destinatario.
12. Preparar para seleccionar los envíos de WhatsApp y email 1 día, 2 días, 3 días y/o una semana antes de la
    cita.

# WhatsApp Cloud API de Meta

Nota de trabajo para sustituir Twilio por Meta WhatsApp Cloud API en Citas Dentista.

## Objetivo

Usar la API oficial de Meta para:

- Enviar mensajes de WhatsApp desde Laravel.
- Recibir estados de entrega y lectura por webhook.
- Guardar en la base de datos el `provider_message_id`, el payload bruto y los timestamps de estado.

## Lo que pide Meta

Para enviar mensajes hace falta, como minimo:

- Una app de Meta con el producto WhatsApp activado.
- Un WhatsApp Business Account.
- Un `phone_number_id`.
- Un access token valido para Graph API.
- Un webhook publico para recibir callbacks de estado.

## Endpoints clave

- Base URL: `https://graph.facebook.com`
- Envio de mensajes: `POST /{version}/{phone_number_id}/messages`
- Version: usar una version fija de Graph API y actualizarla cuando Meta la cambie.

## Payload de envio

Para texto simple, el payload basico es:

```json
{
  "messaging_product": "whatsapp",
  "to": "34XXXXXXXXX",
  "type": "text",
  "text": {
    "preview_url": false,
    "body": "Hola..."
  }
}
```

Puntos importantes:

- `messaging_product` debe ser `whatsapp`.
- `to` debe ir en formato internacional.
- El texto viaja en `text.body`.
- `preview_url` suele ir en `false` para mensajes de recordatorio.

## Webhooks

Meta envia callbacks cuando cambia el estado del mensaje. Para el control del flujo hay que contemplar, como minimo:

- `sent`
- `delivered`
- `read`
- `failed`

El webhook debe:

- Verificar la suscripcion.
- Leer el `message_id` que devuelve Meta al enviar.
- Guardar el estado recibido en `provider_payload`.
- Sincronizar los campos locales de la cita o del mensaje.

## Flujo recomendado para esta app

1. Crear el mensaje local en `whatsapp_messages`.
2. Normalizar el telefono a formato internacional.
3. Enviar el payload a `/messages`.
4. Guardar `provider_message_id` y `provider_payload`.
5. Marcar el mensaje como `sent` o `failed`.
6. Cuando llegue el webhook, actualizar estados de entrega y lectura.

## Encaje con el codigo actual

La base actual ya deja preparado este contrato:

- `config/whatsapp.php` guarda `driver`, `cloud_api.phone_number_id`, `cloud_api.access_token` y `cloud_api.version`.
- `app/Services/WhatsApp/WhatsAppSender.php` ya construye el `POST /messages`.
- `app/Models/WhatsAppMessage.php` ya guarda `provider_message_id`, `provider_payload` y `metadata`.
- `app/Services/WhatsApp/AppointmentDeliveryStatusSyncer.php` ya sincroniza estados desde callbacks.

## Variables de entorno

Las variables minimas para Meta son:

```env
WHATSAPP_DRIVER=cloud_api
WHATSAPP_CLOUD_API_BASE_URL=https://graph.facebook.com
WHATSAPP_CLOUD_API_VERSION=v22.0
WHATSAPP_CLOUD_API_PHONE_NUMBER_ID=1234567890
WHATSAPP_CLOUD_API_ACCESS_TOKEN=EAAG...
WHATSAPP_CLOUD_API_TIMEOUT=15
```

## Restricciones practicas

- Un token de acceso no deberia quedar hardcoded.
- El numero destino debe normalizarse antes de enviar.
- Si el webhook no llega, no se podran marcar estados de entrega o lectura.
- Para mensajes fuera de la ventana de atencion puede hacer falta plantilla aprobada.

## Recursos oficiales

- [WhatsApp Cloud API](https://developers.facebook.com/docs/whatsapp/cloud-api)
- [Get started](https://developers.facebook.com/docs/whatsapp/cloud-api/get-started)
- [Messages reference](https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages)
- [Webhooks](https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks)

## Siguiente paso

Cuando toque implementar, el siguiente paso es sustituir Twilio por un sender de Cloud API que reutilice la normalizacion de telefonos y el guardado de estados que ya existen en el proyecto.

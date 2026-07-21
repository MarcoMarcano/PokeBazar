# Documentación de la API

Este proyecto no expone un backend REST propio para productos o compras. La integración con API que sí incluye la aplicación es la de tipo de cambio para convertir precios de USD a bolívares.

## Endpoint utilizado

Método: GET

```text
https://ve.dolarapi.com/v1/dolares/oficial
```

## Propósito

Obtener una tasa de cambio de referencia para mostrar el equivalente en bolívares de los productos del catálogo.

## Respuesta esperada

Un ejemplo de respuesta es el siguiente:

```json
{
  "fecha": "2026-07-21",
  "moneda": "USD",
  "nombre": "Oficial",
  "compra": 36.2,
  "venta": 36.6,
  "promedio": 36.4
}
```

## Campos usados por la aplicación

La lógica de conversión intenta leer, en este orden:

- `promedio`
- `venta`
- `compra`
- `rate`
- `price`

## Configuración

La URL de la API puede sobrescribirse con la variable de entorno:

```text
EXCHANGE_API_URL
```

Si no se define, la app usa por defecto:

```text
https://ve.dolarapi.com/v1/dolares/oficial
```

## Caché y fallback

El valor obtenido se guarda en la carpeta `storage/` durante una hora para evitar consultas excesivas. Si la API falla o no responde, la aplicación usa un valor de respaldo de `36.0`.

## Ubicación del código

La integración se encuentra en:

- [includes/functions.php](includes/functions.php)

# Ejecutar PokeBazar con Docker

Si prefieres trabajar con contenedores, este proyecto también incluye una configuración básica para levantarlo de manera rápida.

## 1) Levantar los contenedores

Desde la raíz del proyecto, ejecuta:

```bash
docker compose up --build -d
```

Esto crea dos servicios:
- `app`: la aplicación PHP + Apache
- `db`: la base de datos MySQL

## 2) Importar la base de datos

Una vez que los contenedores estén en funcionamiento, importa el archivo SQL:

```bash
docker compose exec -T db mysql -uroot -prootpass pokebazar < database.sql
```

## 3) Abrir la aplicación

Abre la siguiente URL en tu navegador:

```text
http://localhost:8080/
```

## 4) Variables de entorno

El servicio de la app usa estas variables:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `EXCHANGE_API_URL`

Por defecto, la conexión apunta a la base MySQL del contenedor y la conversión de moneda usa la API oficial de DólarApi.

## 5) Conversión de moneda

La aplicación intenta consultar una API pública para convertir precios en USD a bolívares. Si la API no responde, usa un valor de respaldo para evitar detener la experiencia.

# PokeBazar

PokeBazar es una tienda web sencilla para vender cartas y productos relacionados con Pokémon. El proyecto combina PHP, MySQL, HTML y CSS para ofrecer un flujo completo de catálogo, carrito, facturas y administración.

## ¿Qué incluye?

- Catálogo de productos con búsqueda, filtros por categoría y ordenamiento.
- Registro e inicio de sesión para usuarios comunes y administradores.
- Carrito por usuario autenticado, con posibilidad de actualizar o eliminar productos.
- Generación de factura imprimible.
- Panel administrativo para gestionar productos y usuarios.
- Sistema de verificación anti-scalpers con preguntas aleatorias antes de confirmar una compra.
- Conversión de precios a bolívares usando una API pública de tipo de cambio.

## Estructura del proyecto

- `index.php`: página principal del catálogo.
- `login.php` y `register.php`: autenticación de usuarios.
- `cart.php`, `add_to_cart.php`, `update_cart.php`: flujo del carrito.
- `invoice.php` y `quiz.php`: generación de facturas y validación de compra.
- `profile.php`: perfil del usuario.
- `admin/`: panel exclusivo para administradores.
- `config/database.php`: conexión con la base de datos.
- `database.sql`: script de creación de la base de datos y datos iniciales.

## Requisitos

- PHP 8+
- MySQL o MariaDB
- Apache (por ejemplo con XAMPP) o Docker

## Instalación con XAMPP

1. Copia la carpeta del proyecto dentro de la carpeta `htdocs` de XAMPP.
2. Crea una base de datos llamada `pokebazar` en MySQL.
3. Importa el archivo `database.sql` desde phpMyAdmin o desde la línea de comandos.
4. Revisa las credenciales en `config/database.php` y ajústalas si tu instalación usa otra contraseña o host.
5. Abre la aplicación en tu navegador en:

```text
http://localhost/PokeBazar/
```

## Instalación con Docker

Para levantar el proyecto con contenedores, puedes usar el archivo de configuración incluido. Revisa [README-Docker.md](README-Docker.md) para los pasos completos.

## Usuario administrador inicial

- Correo: `admin@pokebazar.com`
- Contraseña: `admin123`

Puedes cambiar esta contraseña desde el perfil del administrador o desde la tabla `users` si lo necesitas.

## Conversión de moneda

Los precios se muestran en dólares y también en bolívares mediante una consulta a la API pública:

```text
https://ve.dolarapi.com/v1/dolares/oficial
```

La tasa se almacena en caché por un tiempo para evitar consultas excesivas. Si prefieres usar otra fuente, puedes cambiar la variable `EXCHANGE_API_URL` desde Docker o el valor por defecto en la función de conversión.

## Notas de funcionamiento

- El proceso de compra incluye una verificación breve para reducir compras automáticas o poco honestas.
- Cuando se genera una factura, el stock de los productos se actualiza automáticamente.
- La factura incluye una marca de agua de fondo para uso personal.

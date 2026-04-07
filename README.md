# PokeBazar

PokeBazar es un e-commerce de cartas de Pokemon desarrollado con PHP, HTML, JavaScript y MySQL.

## Funcionalidades

- Catalogo de productos con busqueda, filtro por categoria y ordenamiento.
- Registro e inicio de sesion con roles de usuario y administrador.
- Carrito por usuario autenticado con actualizacion y eliminacion de productos.
- Perfil con edicion de datos personales y cambio de contrasena con confirmacion.
- Generacion de factura con vista imprimible.
- Panel de administrador para gestionar productos, usuarios y ascender roles.

## Estructura

- `index.php`: catalogo principal.
- `login.php` y `register.php`: autenticacion.
- `cart.php`, `add_to_cart.php`, `update_cart.php`: flujo del carrito.
- `profile.php`: perfil del usuario.
- `invoice.php`: factura imprimible.
- `admin/`: panel exclusivo de administracion.
- `database.sql`: script de creacion de base de datos y datos iniciales.

## Instalacion

1. Copia la carpeta del proyecto dentro de tu servidor local PHP, por ejemplo `htdocs` en XAMPP.
2. Crea la base de datos importando `database.sql` en MySQL.
3. Revisa las credenciales en `config/database.php` y ajustalas si tu entorno usa otros datos.
4. Abre `http://localhost/PokeBazar/index.php` en el navegador.

## Usuario inicial

- Correo: `admin@pokebazar.com`
- Contrasena: `admin123`

Puedes cambiar la contrasena inicial, ingresando con el usuario administrador y actualizandola desde el perfil o la tabla `users`.

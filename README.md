# Raíz Local | Tolimán, Querétaro

Plataforma web para fomentar el comercio local y el intercambio de productos en comunidades rurales.

## Descripción

Raíz Local es un proyecto enfocado en conectar a productores y habitantes de Tolimán, Querétaro, mediante un espacio digital donde pueden consultar y publicar productos de su comunidad.

La plataforma busca facilitar la difusión de productos locales, apoyar a pequeños productores y promover el consumo dentro de la comunidad.

## Tecnologías utilizadas

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- PDO
- Composer
- PHP dotenv

## Funcionalidades implementadas

- Registro e inicio de sesión de usuarios.
- Gestión de sesiones.
- Perfiles de vendedor.
- Publicación, edición y administración de productos.
- Consulta de catálogo de productos.
- Gestión de productos favoritos.
- Edición de información del perfil.
- Conexión a base de datos MySQL.

## Estructura del proyecto

```text
RaizLocalToliman/
├── assets/
│   ├── css/
│   ├── img/
│   └── js/
├── database/
│   └── init/
│       └── raiz_local_estructura.sql
├── acceso.php
├── cliente.php
├── vendedor.php
├── perfil.php
├── favoritos.php
├── conexion.php
├── cerrar_sesion.php
├── index.php
├── composer.json
└── .env.example
```

## Instalación y configuración

1. Clonar el repositorio:

   ```bash
   git clone https://github.com/LupitaPerez01/raiz-local.git
   ```

2. Entrar a la carpeta del proyecto:

   ```bash
   cd raiz-local
   ```

3. Instalar las dependencias:

   ```bash
   composer install
   ```

4. Crear un archivo `.env` tomando como referencia `.env.example` y configurar los datos de conexión a MySQL.

5. Crear la base de datos `raiz_local` e importar el archivo `database/init/raiz_local_estructura.sql`.

6. Configurar Apache y abrir el proyecto desde el navegador:

   ```text
   http://localhost/raiz-local/index.php
   ```

## Base de datos

El proyecto utiliza MySQL para organizar la información mediante tablas relacionadas, entre ellas:

- usuarios
- perfiles_vendedor
- productos
- favoritos
- pedidos
- pedidos_vendedor
- detalle_pedido

## Contexto del proyecto

Desarrollado como proyecto académico de Ingeniería en Sistemas Computacionales, con enfoque en el desarrollo web y la administración de bases de datos para apoyar el comercio local.

## Estado

Proyecto en desarrollo.

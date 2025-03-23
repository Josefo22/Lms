# Sistema de Inventario de Equipos Informáticos

Un sistema de gestión de inventario para equipos informáticos, desarrollado con PHP y MySQL.

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache, Nginx, etc.)
- PDO PHP Extension
- Navegador web moderno

## Instalación

1. Clonar o descargar el repositorio en la carpeta raíz de su servidor web (por ejemplo, htdocs para XAMPP)

```
git clone https://github.com/josefo22/LMS_IT_Inventory.git
```

2. Crear la base de datos y las tablas necesarias

   - Acceder a phpMyAdmin (http://localhost/phpmyadmin)
   - Crear una nueva base de datos llamada `lms_it_inventory`
   - Importar el archivo `database.sql` incluido en el proyecto

   O si tienes acceso a la línea de comandos de MySQL:

```
mysql -u root -p < database.sql
```

3. Configurar la conexión a la base de datos

   - Editar el archivo `config/database.php` si es necesario
   - Por defecto, está configurado para:
     - Host: localhost
     - Usuario: root
     - Contraseña: (vacía)
     - Base de datos: lms_it_inventory

4. Acceder al sistema

   - URL: http://localhost/LMS_IT_Inventory/
   - Email: admin@example.com
   - Contraseña: admin123

## Solución de problemas

Si encuentras errores como "Column not found" al intentar acceder al sistema:

1. Asegúrate de que has importado correctamente el archivo `database.sql`
2. Verifica que la tabla `users` contenga las columnas:
   - id
   - username
   - email
   - password
   - role
   - created_at
3. Si importas manualmente el SQL, asegúrate de ejecutar primero los comandos CREATE TABLE y luego los INSERT

## Estructura del Proyecto

```
LMS_IT_Inventory/
├── app/
│   ├── controllers/      # Controladores
│   ├── models/           # Modelos
│   ├── views/            # Vistas
│   ├── includes/         # Componentes reutilizables (header, footer, etc.)
│   └── helpers/          # Funciones de ayuda
├── assets/
│   ├── css/              # Hojas de estilo
│   ├── js/               # Scripts de JavaScript
│   └── img/              # Imágenes
├── config/               # Archivos de configuración
├── public/               # Archivos públicos
├── index.php             # Punto de entrada principal
├── login.php             # Página de inicio de sesión
├── logout.php            # Script de cierre de sesión
├── database.sql          # Script SQL para crear la base de datos
└── README.md             # Este archivo
```

## Características

- Gestión de usuarios con diferentes roles
- Dashboard con estadísticas y gráficos
- Inventario de equipos informáticos
- Asignación de equipos a usuarios
- Registro de mantenimientos
- Generación de reportes
- Interfaz responsiva

## Personalización

Para personalizar el sistema según sus necesidades:

1. Editar el archivo `config/config.php` para cambiar el nombre del sitio y otras configuraciones
2. Modificar los estilos en `assets/css/style.css`
3. Extender las funcionalidades añadiendo nuevos controladores y vistas

## Seguridad

Este sistema incluye:
- Protección contra inyección SQL mediante PDO con parámetros preparados
- Contraseñas hash con bcrypt
- Control de sesiones
- Validación de entrada

## Licencia

Este proyecto está disponible bajo la licencia MIT. 
# MINLEX - Sistema de Gestión de Producción

## Descripción del Proyecto
MINLEX es un sistema de gestión de producción diseñado para optimizar y controlar los procesos productivos. El sistema incluye gestión de usuarios, control de roles, administración de clientes y seguimiento de órdenes de producción.

## Características Principales
- Sistema de autenticación y autorización
- Gestión de usuarios y roles
- Administración de clientes
- Control de órdenes de producción
- Seguimiento de procesos productivos
- Generación de reportes

## Estructura del Proyecto
```
├── components/         # Componentes reutilizables
├── config/            # Configuración de la base de datos
├── controllers/       # Controladores MVC
├── css/               # Estilos CSS
├── documents/         # Documentación técnica
├── js/                # Scripts JavaScript
├── models/            # Modelos de datos
├── resources/         # Recursos estáticos
└── views/             # Vistas de la aplicación
```

## Tecnologías Utilizadas
- PHP
- MySQL
- Bootstrap 5.3
- JavaScript

## Módulos del Sistema

### 1. Gestión de Usuarios
- Creación y administración de usuarios
- Asignación de roles y permisos
- Departamentos disponibles:
  - Corte
  - Calidad
  - Producción
  - Compras
  - Almacén
  - Costura
  - Administración

### 2. Gestión de Clientes
- Registro de información de clientes
- Datos de contacto y dirección
- Seguimiento de pedidos por cliente

### 3. Proceso Productivo
- Control de órdenes de producción
- Seguimiento de estado de producción
- Reportes de producción

## Instalación

1. Clonar el repositorio
2. Configurar el servidor web (XAMPP recomendado)
3. Importar la base de datos desde `bd.sql`
4. Configurar la conexión en `config/database.php`
5. Acceder al sistema mediante el navegador

## Requisitos del Sistema
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/XAMPP)

## Seguridad
- Autenticación de usuarios requerida
- Control de acceso basado en roles
- Sesiones seguras

## Documentación Adicional
Para más información técnica, consultar los documentos en la carpeta `documents/`:
- Flujo y descripción técnica del proceso productivo
- Diagrama relacional de la base de datos

## Desarrollado por
ECODE | Software Development
# 📝 Descripción del Proyecto: API REST Dinámica en PHP

Este proyecto consiste en una **API REST genérica y dinámica** desarrollada en PHP nativo que permite realizar operaciones CRUD (Create, Read, Update, Delete) sobre cualquier tabla de una base de datos MySQL de forma automatizada, sin necesidad de escribir controladores ni modelos específicos para cada tabla nueva.

## 📌 Reglas de Diseño de la Base de Datos

Para que la API funcione de forma dinámica, la base de datos debe seguir las siguientes convenciones:

1. **Nombres de Tablas y Columnas:**
   * Las tablas deben nombrarse en **plural** (ejemplo: `usuarios`, `cursos`).
   * Los campos internos deben llevar un sufijo o prefijo relacionado con el nombre en **singular** de la tabla (ejemplo: tabla `courses` -> columnas `id_course`, `title_course`, `description_course`).
2. **Estructura Estándar de Columnas:**
   * La primera columna de cada tabla debe ser su clave primaria autoincremental (`id_singular`).
   * Las dos últimas columnas deben ser:
     * `date_created_singular`: Almacena la fecha de creación del registro.
     * `date_updated_singular`: Almacena la fecha de la última actualización del registro.

---

## 📂 Estructura General del Proyecto

```text
apirest-dinamica/
├── .htaccess                  # Reglas de reescritura de URL (enrutamiento hacia index.php)
├── index.php                  # Punto de entrada principal (Bootstrap) y configuración de CORS
├── descripcion.md             # Descripción general del proyecto
├── flujo_arquitectura.md      # Mapa de flujo y arquitectura detallada
├── SKILL.md                   # Habilidad personalizada del asistente
├── composer.json              # Dependencias de composer (como firebase/php-jwt)
├── errores_log                # Log interno de errores del sistema
├── controllers/               # Controladores de la API (Cerebro)
│   ├── get.controller.php
│   ├── post.controller.php
│   ├── put.controller.php
│   ├── delete.controller.php
│   └── routes.contoller.php
├── models/                    # Modelos de la API (Acceso seguro a datos)
│   ├── connection.php         # Conexión PDO, autenticación JWT y API key
│   ├── get.model.php
│   ├── post.model.php
│   ├── put.model.php
│   └── delete.model.php
├── routes/                    # Definición de rutas y servicios por método
│   ├── routes.php             # Semáforo de control de peticiones
│   └── services/              # Controladores de servicios
│       ├── get.php
│       ├── post.php
│       ├── put.php
│       └── delete.php
└── Database/                  # Respaldos de base de datos
    └── SQL/
        ├── database-1.sql
        ├── database-2.sql
        └── database-3.sql
```
# 🗺️ Mapa de Flujo y Arquitectura de la API REST Dinámica

¡Hola! Como estudiante de desarrollo Full Stack, entender cómo se comunican los archivos entre sí es el pilar más importante para construir aplicaciones robustas, escalables y seguras. 

Esta arquitectura sigue una variante del patrón **MVC (Modelo-Vista-Controlador)** adaptada para una API REST, donde:
*   **Rutas (`routes/`):** Actúan como el "Tráfico", recibiendo la petición HTTP y decidiendo a qué servicio dirigirla.
*   **Controladores (`controllers/`):** Actúan como el "Cerebro", validando datos, aplicando lógica de negocio y comunicando las rutas con los modelos.
*   **Modelos (`models/`):** Actúan como las "Manos", comunicándose directamente con la base de datos (BD) a través de consultas SQL preparadas de forma segura.

---

## 🔄 Ciclo de Vida de una Petición (Request-Response)

El siguiente diagrama muestra exactamente el orden cronológico en que se conectan y ejecutan tus archivos desde que realizas una petición en Postman o en el navegador hasta que obtienes los datos:

```mermaid
sequenceDiagram
    autonumber
    actor Cliente as 💻 Cliente (Postman/Navegador)
    participant Index as 🔑 index.php<br/>(Punto de Entrada)
    participant RoutesCtrl as 🎛️ routesController<br/>(controllers/routes.contoller.php)
    participant Routes as 🚦 routes.php<br/>(routes/routes.php)
    participant GetService as 🛠️ get.php<br/>(routes/services/get.php)
    participant GetCtrl as 🧠 GetController<br/>(controllers/get.controller.php)
    participant GetModel as 💾 GetModel<br/>(models/get.model.php)
    participant Conn as 🔌 Connection<br/>(models/connection.php)
    database DB as 🗄️ Base de Datos

    Cliente->>Index: 1. Petición HTTP GET /apirest-dinamica/usuarios
    Index->>RoutesCtrl: 2. Crea instancia y llama a index()
    RoutesCtrl->>Routes: 3. Incluye el enrutador principal (include)
    Note over Routes: Evalúa la URL y el método HTTP (GET, POST, etc.)
    Routes->>GetService: 4. Si es GET, incluye el servicio (include)
    Note over GetService: Obtiene el nombre de la tabla (ej. "usuarios") de la URL
    GetService->>GetCtrl: 5. Llama a GetController::getData($table)
    GetCtrl->>GetModel: 6. Llama a GetModel::getData($table)
    GetModel->>Conn: 7. Llama a Connection::connetcDataBase()
    Conn->>DB: 8. Ejecuta la consulta SQL de forma segura
    DB-->>Conn: 9. Devuelve las filas encontradas
    Conn-->>GetModel: 10. Retorna el objeto de conexión activa
    GetModel-->>GetCtrl: 11. Devuelve un Array de Objetos (los registros)
    GetCtrl-->>GetService: 12. Devuelve la respuesta al servicio
    Note over GetService: Codifica los datos en formato JSON
    GetService-->>Cliente: 13. Retorna la respuesta JSON con código HTTP (200, 404, etc.)
```

---

## 📂 Descripción Detallada de cada Archivo y sus Conexiones

### 1. `index.php` (Punto de entrada principal)
*   **Propósito:** Es el archivo que recibe **absolutamente todas** las solicitudes del servidor web (gracias al archivo `.htaccess`). Se encarga de encender los reportes de errores (para desarrollo) e inicializar el enrutador.
*   **Conexión de entrada:** Viene directamente de la petición HTTP del usuario en el navegador o Postman.
*   **Conexión de salida:** Llama a `controllers/routes.contoller.php` para delegar el control.

### 2. `controllers/routes.contoller.php` (El despachador)
*   **Propósito:** Contiene la clase `routesController` cuyo único objetivo es incluir el archivo de definición de rutas real.
*   **Conexión de entrada:** Es instanciado y ejecutado por `index.php`.
*   **Conexión de salida:** Incluye a `routes/routes.php`.

### 3. `routes/routes.php` (El semáforo de la API)
*   **Propósito:** Analiza la URL utilizando `$_SERVER['REQUEST_URI']`, la divide en partes usando `/` y determina si el cliente está solicitando una ruta válida. También evalúa el método HTTP (`GET`, `POST`, `PUT`, `DELETE`).
*   **Conexión de entrada:** Incluido por `routesController`.
*   **Conexión de salida:**
    *   Si no se ingresó ninguna tabla en la URL, devuelve un error 404 en formato JSON directamente.
    *   Si es una petición `GET`, incluye a `routes/services/get.php`.

### 4. `routes/services/get.php` (El integrador del servicio GET)
*   **Propósito:** Procesa específicamente las peticiones de lectura (`GET`). Toma el nombre de la tabla solicitado desde la URL y lo pasa al controlador.
*   **Conexión de entrada:** Incluido por `routes/routes.php`.
*   **Conexión de salida:** Debe comunicarse con `controllers/get.controller.php` para obtener los datos reales de la base de datos y retornarlos al cliente con `json_encode()`.

### 5. `controllers/get.controller.php` (El cerebro de las peticiones GET)
*   **Propósito:** Clase `GetController` que administra y valida las peticiones GET. En una API real, aquí verificarías si el usuario tiene permisos, si la tabla existe en una lista blanca de seguridad, o si los parámetros son correctos antes de ir a la Base de Datos.
*   **Conexión de entrada:** Llama sus métodos estáticos desde `routes/services/get.php`.
*   **Conexión de salida:** Solicita la información al modelo llamando a `GetModel::getData()`.

### 6. `models/get.model.php` (El puente seguro con la BD)
*   **Propósito:** Clase `GetModel` encargada de armar y ejecutar las consultas SQL en la base de datos de manera segura mediante sentencias preparadas de PDO.
*   **Conexión de entrada:** Llamado por `GetController::getData()`.
*   **Conexión de salida:**
    *   Usa la conexión establecida en `models/connection.php`.
    *   Devuelve un arreglo estructurado con los datos de las tablas al controlador.

### 7. `models/connection.php` (El canal físico)
*   **Propósito:** Clase `Connection` que provee las credenciales (base de datos, usuario, contraseña) y retorna una conexión activa tipo **PDO** (PHP Data Objects).
*   **Conexión de entrada:** Utilizada por los modelos (`GetModel`, etc.) para poder interactuar con MySQL.
*   **Conexión de salida:** Conexión directa al servidor MySQL de tu servidor WampServer.

---

## 🛡️ Alerta de Seguridad y Buenas Prácticas (OWASP Top 10)

> [!WARNING]
> En `models/get.model.php`, la consulta se define actualmente como:
> ```php
> $sql = "SELECT * FROM $table";
> ```
> Aunque utilizas sentencias preparadas PDO (`prepare($sql)`), **los nombres de las tablas o columnas no se pueden parametrizar en PDO** con marcadores (como `?` o `:table`). Concatenar `$table` directamente de la URL representa un riesgo de **Inyección SQL de Identificadores**.
>
> **Solución recomendada:** Implementar una lista blanca (whitelist) de tablas permitidas en tu controlador antes de pasar el string al modelo. Así garantizas que nadie pueda consultar tablas sensibles (como `usuarios_passwords`, `configuraciones_sistema`, etc.).

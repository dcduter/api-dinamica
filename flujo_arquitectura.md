# 🗺️ Mapa de Flujo y Arquitectura de la API REST Dinámica

¡Hola! Como estudiante de desarrollo Full Stack, entender cómo se comunican los archivos entre sí es el pilar más importante para construir aplicaciones robustas, escalables y seguras.

Esta arquitectura sigue una variante del patrón **MVC (Modelo-Vista-Controlador)** adaptada para una API REST, donde:
*   **Rutas (`routes/`):** Actúan como el "Tráfico", recibiendo la petición HTTP y decidiendo a qué servicio dirigirla.
*   **Controladores (`controllers/`):** Actúan como el "Cerebro", recibiendo parámetros, controlando la lógica de negocio y comunicando las rutas con los modelos.
*   **Modelos (`models/`):** Actúan como las "Manos", comunicándose directamente con la base de datos (BD) mediante consultas en PDO.

---

## 📋 Referencia de Variables para Consultas SQL

| Variable | Referencia en SQL | Descripción |
| :--- | :--- | :--- |
| **`select`** | `SELECT ...` | Lista de columnas (separadas por coma) que quieres obtener de la base de datos. |
| **`table`** | `FROM ...` | El nombre de la tabla principal para la consulta. |
| **`rel`** | `JOIN ...` | Lista de tablas relacionadas (separadas por coma) para hacer `INNER JOIN`s. |
| **`type`** | `ON ...` | Se usa junto con `rel` para definir la lógica de los nombres de los campos de relación (ej. foreign keys) en los `JOIN`. |
| **`linkTo`** | `WHERE ...` | El nombre de la columna que funcionará como filtro (`WHERE campo = ...`). |
| **`equalTo`** | `= ...` | El valor exacto que debe tener la columna definida en `linkTo`. |
| **`search`** | `LIKE ...` | El valor/palabra clave que se utilizará para buscar dentro de una columna (búsquedas parciales). |
| **`between1`** | `BETWEEN ... AND` | El valor inferior (límite inicial) para una búsqueda de rango. |
| **`between2`** | `... AND ...` | El valor superior (límite final) para una búsqueda de rango. |
| **`filterTo`** | `SELECT / WHERE` | Una columna adicional que se incluye para aplicar filtros complejos o rangos sin perder la estructura principal. |
| **`inTo`** | `IN (...)` | Indica que se debe realizar un filtrado utilizando una lista de valores permitidos (`WHERE columna IN (val1, val2)`). |
| **`orderBy`** | `ORDER BY ...` | La columna por la cual se ordenarán los resultados. |
| **`orderMode`** | `ASC/DESC` | El sentido del orden (`ASC` para ascendente, `DESC` para descendente). |
| **`startAt`** | `LIMIT ...` | El índice inicial para paginación. |
| **`endAt`** | `OFFSET ...` | La cantidad de registros a obtener en la paginación. |

---

## 🔄 Ciclo de Vida de una Petición (Request-Response)

### 1. Flujo para Petición de Lectura (GET)

El siguiente diagrama muestra exactamente el orden cronológico en que se conectan y ejecutan tus archivos desde que realizas una petición GET (incluyendo parámetros de columnas, filtrado y ordenamiento) hasta que obtienes los datos en formato JSON:

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

    Cliente->>Index: 1. GET /usuarios?select=nombre,email&linkTo=status&equalTo=activo&orderBy=id&orderMode=DESC&startAt=0&endAt=5
    Index->>RoutesCtrl: 2. Crea instancia y llama a index()
    RoutesCtrl->>Routes: 3. Incluye el enrutador principal (include)
    Note over Routes: Evalúa la URL y el método HTTP (GET)
    Routes->>GetService: 4. Incluye el servicio GET (include)
    Note over GetService: Valida y extrae parámetros $_GET (select, orderBy, orderMode, linkTo, equalTo, startAt, endAt)
    
    alt ¿Vienen parámetros de filtro (linkTo y equalTo)?
        GetService->>GetCtrl: 5a. Llama a getDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
        GetCtrl->>GetModel: 6a. Llama a GetModel::getDataFilter(...)
        Note over GetModel: Procesa filtros, prepara placeholders (:param)<br/>y concatena ORDER BY y LIMIT
    else No vienen parámetros de filtro
        GetService->>GetCtrl: 5b. Llama a getData($table, $select, $orderBy, $orderMode, $startAt, $endAt)
        GetCtrl->>GetModel: 6b. Llama a GetModel::getData(...)
        Note over GetModel: Concatena SELECT, ORDER BY y LIMIT si aplican
    end

    GetModel->>Conn: 7. Llama a Connection::connectDataBase()
    Conn->>DB: 8. Ejecuta la consulta SQL (bindParam si es filtrado)
    DB-->>Conn: 9. Devuelve las filas encontradas
    Conn-->>GetModel: 10. Retorna la conexión activa y resultados
    GetModel-->>GetCtrl: 11. Devuelve Array de Objetos (los registros)
    GetCtrl->>GetCtrl: 12. Llama a fncResponse($response)
    Note over GetCtrl: Formatea a JSON y establece el código HTTP de respuesta
    GetCtrl-->>Cliente: 13. Retorna la respuesta JSON con código HTTP (200 o 404)
```

---

## 📂 Descripción Detallada de cada Archivo y sus Conexiones

### 1. [index.php](file:///c:/wamp64/www/apirest-dinamica/index.php) (Punto de entrada principal)
*   **Propósito:** Recibe todas las solicitudes del servidor web (redireccionadas por el `.htaccess`). Inicializa la configuración de errores del sistema, define las cabeceras CORS de forma global y arranca la API instanciando el despachador de rutas.
*   **Conexión de entrada:** Petición HTTP directa del cliente.
*   **Conexión de salida:** Llama a `controllers/routes.contoller.php`.

### 2. [routes.contoller.php](file:///c:/wamp64/www/apirest-dinamica/controllers/routes.contoller.php) (El despachador)
*   **Propósito:** Define la clase `routesController` cuyo método `index()` incluye el archivo de definición de enrutamiento del backend.
*   **Conexión de entrada:** Instanciado por `index.php`.
*   **Conexión de salida:** Incluye el archivo de rutas `routes/routes.php`.

### 3. [routes.php](file:///c:/wamp64/www/apirest-dinamica/routes/routes.php) (El semáforo de la API)
*   **Propósito:** Analiza la URI de la petición, determina a qué tabla se quiere acceder, y evalúa el método HTTP correspondiente (`GET`, `POST`, `PUT`, `DELETE`). Adicionalmente valida la presencia de la clave de API (`ApiKey`) en las cabeceras de autorización de la solicitud antes de otorgar acceso (a menos que la tabla esté en la lista de acceso público).
*   **Conexión de entrada:** Incluido por `routesController`.
*   **Conexión de salida:** Incluye los servicios correspondientes (`routes/services/get.php`, `post.php`, `put.php` o `delete.php`).

### 4. Servicios (`routes/services/`)
*   **[get.php](file:///c:/wamp64/www/apirest-dinamica/routes/services/get.php):** Extrae y valida los parámetros de la URL para consultas de tipo GET (filtros, rangos, búsquedas, relaciones o consultas simples).
*   **[post.php](file:///c:/wamp64/www/apirest-dinamica/routes/services/post.php):** Procesa datos enviados en el cuerpo de la petición. Maneja el registro de usuarios (`register=true`), inicio de sesión (`login=true`) o inserción genérica de registros previa verificación del token JWT.
*   **[put.php](file:///c:/wamp64/www/apirest-dinamica/routes/services/put.php):** Captura los datos a editar a través de `php://input`, valida que la tabla y columnas existan, verifica el token de seguridad del usuario y ejecuta la actualización.
*   **[delete.php](file:///c:/wamp64/www/apirest-dinamica/routes/services/delete.php):** Valida la existencia de la tabla/columna clave, comprueba el token de seguridad y llama al controlador para eliminar el registro seleccionado por ID.

### 5. Controladores (`controllers/`)
*   **[get.controller.php](file:///c:/wamp64/www/apirest-dinamica/controllers/get.controller.php):** Valida las peticiones GET, llama al modelo correspondiente y formatea las respuestas a JSON estructurado.
*   **[post.controller.php](file:///c:/wamp64/www/apirest-dinamica/controllers/post.controller.php):** Contiene la lógica de negocio para el registro, inicio de sesión (encriptación y generación de tokens JWT) e inserción.
*   **[put.controller.php](file:///c:/wamp64/www/apirest-dinamica/controllers/put.controller.php):** Canaliza las solicitudes de edición hacia el modelo y devuelve la respuesta.
*   **[delete.controller.php](file:///c:/wamp64/www/apirest-dinamica/controllers/delete.controller.php):** Llama al modelo de borrado y formatea la respuesta JSON.

### 6. Modelos (`models/`)
*   **[connection.php](file:///c:/wamp64/www/apirest-dinamica/models/connection.php):** Provee la conexión segura a la base de datos (PDO), valida la existencia de tablas/columnas consultando `information_schema.columns`, y gestiona la generación y validación de tokens JWT.
*   **[get.model.php](file:///c:/wamp64/www/apirest-dinamica/models/get.model.php):** Construye consultas dinámicas SELECT para búsquedas, rangos, relaciones y filtros parametrizados.
*   **[post.model.php](file:///c:/wamp64/www/apirest-dinamica/models/post.model.php):** Genera consultas SQL dinámicas de tipo `INSERT INTO` vinculando parámetros de forma segura con `bindParam`.
*   **[put.model.php](file:///c:/wamp64/www/apirest-dinamica/models/put.model.php):** Construye consultas SQL dinámicas de tipo `UPDATE` parametrizando los valores modificados.
*   **[delete.model.php](file:///c:/wamp64/www/apirest-dinamica/models/delete.model.php):** Construye y ejecuta consultas `DELETE FROM` dinámicas filtradas de forma segura por el identificador del registro.

---

## 🛡️ Reporte Detallado de Seguridad y Riesgos (Análisis OWASP)

Como parte de tu formación en ciberseguridad, es crucial analizar críticamente el código actual de la API. A continuación, se detallan los riesgos de seguridad detectados y cómo solucionarlos:

### 1. 🚨 Credenciales expuestas de la Base de Datos
*   **Ubicación:** `Connection::infoDataBase()` en [connection.php](file:///c:/wamp64/www/apirest-dinamica/models/connection.php) (Líneas 31-35).
*   **Riesgo:** Las credenciales de acceso a la base de datos local (`database-1`, usuario `root` sin contraseña) están escritas directamente en el código fuente. Si subes este código a un repositorio público como GitHub, tus credenciales quedarán expuestas para cualquiera.
*   **Mitigación:** Mover estos datos a un archivo de configuración externa o variables de entorno (`.env`) usando librerías como `vlucas/phpdotenv`.

### 2. 🚨 Clave de API Secreta Hardcodeada (Exposición de Llaves de API)
*   **Ubicación:** `Connection::apiKey()` en [connection.php](file:///c:/wamp64/www/apirest-dinamica/models/connection.php) (Línea 195).
*   **Riesgo:** La clave estática `"api_XhoKafOhvrBeoiqkc2rxilhAOxd1tTJB"` que autoriza el acceso a endpoints restringidos está escrita en duro. Cualquiera con acceso al código fuente o capaz de decompilar la app cliente conocerá la API Key global.
*   **Mitigación:** Almacenar la API Key en variables de entorno del servidor.

### 3. 🚨 Clave de Firma JWT Hardcodeada
*   **Ubicación:** `PostController::postRegister()` y `PostController::postLogin()` en [post.controller.php](file:///c:/wamp64/www/apirest-dinamica/controllers/post.controller.php) (Líneas 77, 152, 196).
*   **Riesgo:** La clave para firmar los tokens JWT (`'lksdjflkj3klj4lskdjfklnj23asdfgh'`) está escrita en el código. Si un atacante conoce esta clave, puede fabricar tokens válidos con cualquier identidad y saltarse toda la seguridad del backend.
*   **Mitigación:** Almacenar la firma secreta del JWT en el archivo `.env` del servidor.

### 4. 🚨 Uso Inseguro de Criptografía para Contraseñas (OWASP A02:2021)
*   **Ubicación:** `PostController::postRegister()` y `PostController::postLogin()` en [post.controller.php](file:///c:/wamp64/www/apirest-dinamica/controllers/post.controller.php) (Líneas 45, 142).
*   **Riesgo:** Estás utilizando la función antigua `crypt()` con un salt estático (fijo) e idéntico para todos los usuarios: `'$2a$07$usesomesillystringforsalt$'`. Esto significa que si dos usuarios eligen la contraseña `123456`, ambos tendrán exactamente el mismo hash guardado en la base de datos. Un atacante con acceso a la base de datos podría adivinar fácilmente las contraseñas usando tablas arcoíris (Rainbow Tables).
*   **Mitigación:** Usar las funciones estándar y seguras de PHP:
    *   Para guardar la contraseña: `password_hash($password, PASSWORD_BCRYPT)`
    *   Para validar la contraseña en el login: `password_verify($password, $hashSaved)`
    *   *Nota:* PHP genera automáticamente un salt aleatorio y seguro para cada contraseña bajo esta función.

### 5. 🚨 Vulnerabilidad Crítica de Inyección SQL en Búsquedas y Rangos (OWASP A03:2021)
*   **Ubicaciones:**
    *   `GetModel::getDataSearch()` y `getRelDataSearch()` en [get.model.php](file:///c:/wamp64/www/apirest-dinamica/models/get.model.php) (Líneas 348 y 450).
    *   `GetModel::getDataRange()` y `getRelDataRange()` en [get.model.php](file:///c:/wamp64/www/apirest-dinamica/models/get.model.php) (Líneas 530 y 603).
*   **Riesgo:** Estás interpolando directamente parámetros recibidos del usuario a través de GET en la consulta SQL sin usar sentencias preparadas ni validación alguna:
    *   En búsquedas: `LIKE '%$searchToArray[0]%'`
    *   En rangos: `BETWEEN '$between1' AND '$between2'`
    Un atacante puede enviar un valor como `?search=valor' OR '1'='1` o `?between1=1' OR 1=1 --` y manipular por completo la base de datos, logrando robar información sensible o corromper datos.
*   **Mitigación:** Usar parámetros preparados en PDO (`:search`, `:between1`, `:between2`) y enlazarlos de forma segura mediante `$stmt->bindParam()` o pasarlos en el método execute.

### 6. 🚨 Inyección SQL de Identificadores (Tablas y Columnas)
*   **Ubicación:** `Connection::getColumnsData()` en [connection.php](file:///c:/wamp64/www/apirest-dinamica/models/connection.php) (Línea 110) y en todas las consultas concatenadas (`$table`, `$select`, `$orderBy`).
*   **Riesgo:** El nombre de la tabla `$table` se concatena directamente en la consulta del esquema de datos: `"SELECT ... WHERE table_name = '$table'"`. Dado que la tabla proviene directamente de la URL, un usuario malintencionado puede alterar el valor para inyectar SQL a nivel de metadatos.
*   **Mitigación:** Validar los nombres de las tablas y campos recibidos contra una **lista blanca (whitelist)** de tablas existentes autorizadas para consumo de la API antes de construir la consulta.

### 7. 🚨 Evasión Completa de Autenticación mediante el Parámetro `except` (Backdoor)
*   **Ubicación:** `routes/services/post.php` y `routes/services/put.php` (Líneas 68 y 66 respectivamente).
*   **Riesgo:** El código contiene una condición que anula el requerimiento de token:
    ```php
    if($_GET["token"] == "no" && isset($_GET["except"]))
    ```
    Si esta condición se cumple, el servidor permite realizar inserciones (POST) o modificaciones (PUT) en las tablas sin validar el JWT. Esto expone a la base de datos a que cualquier usuario no autenticado inserte o altere datos falsos con solo agregar `?token=no&except=cualquier_columna`.
*   **Mitigación:** Eliminar por completo esta condición. Si hay tablas o columnas con lógica de acceso diferente, definir reglas de autorización basadas en roles (RBAC) o rutas públicas independientes y seguras.

### 8. 🚨 Typo en cabeceras CORS y Exposición de Errores (OWASP A05:2021)
*   **Ubicación:** `index.php` (Líneas 13, 22).
*   **Riesgo:**
    *   La línea `header('Accesss-Control-Allow-Origin: *');` contiene un error ortográfico en la palabra `Accesss` (con tres 's'). Esto puede hacer que los navegadores ignoren la regla CORS.
    *   `ini_set('display_errors', 1);` está activo. Mostrar errores detallados directamente en la pantalla del usuario en un entorno de producción revela rutas internas, nombres de tablas y datos que facilitan los ataques.
*   **Mitigación:** corregir el header CORS a `Access-Control-Allow-Origin` y configurar `display_errors` a `0` para entornos de producción, manteniendo la escritura segura en logs internos.

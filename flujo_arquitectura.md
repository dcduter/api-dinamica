# 🗺️ Mapa de Flujo y Arquitectura de la API REST Dinámica

¡Hola! Como estudiante de desarrollo Full Stack, entender cómo se comunican los archivos entre sí es el pilar más importante para construir aplicaciones robustas, escalables y seguras.

Esta arquitectura sigue una variante del patrón **MVC (Modelo-Vista-Controlador)** adaptada para una API REST, donde:
*   **Rutas (`routes/`):** Actúan como el "Tráfico", recibiendo la petición HTTP y decidiendo a qué servicio dirigirla.
*   **Controladores (`controllers/`):** Actúan como el "Cerebro", recibiendo parámetros, controlando la lógica de negocio y comunicando las rutas con los modelos.
*   **Modelos (`models/`):** Actúan como las "Manos", comunicándose directamente con la base de datos (BD) mediante consultas preparadas en PDO de forma segura.

---

## 🔄 Ciclo de Vida de una Petición con Filtros y Ordenamiento (Request-Response)

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

    Cliente->>Index: 1. GET /usuarios?select=nombre,email&linkTo=status&equalTo=activo&orderBy=id&orderMode=DESC
    Index->>RoutesCtrl: 2. Crea instancia y llama a index()
    RoutesCtrl->>Routes: 3. Incluye el enrutador principal (include)
    Note over Routes: Evalúa la URL y el método HTTP (GET)
    Routes->>GetService: 4. Incluye el servicio GET (include)
    Note over GetService: Extrae parámetros $_GET (select, orderBy, orderMode, linkTo, equalTo)
    
    alt ¿Vienen parámetros de filtro (linkTo y equalTo)?
        GetService->>GetCtrl: 5a. Llama a getDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode)
        GetCtrl->>GetModel: 6a. Llama a GetModel::getDataFilter(...)
        Note over GetModel: Procesa filtros, prepara placeholders (:param)<br/>y concatena ORDER BY
    else No vienen parámetros de filtro
        GetService->>GetCtrl: 5b. Llama a getData($table, $select, $orderBy, $orderMode)
        GetCtrl->>GetModel: 6b. Llama a GetModel::getData(...)
        Note over GetModel: Concatena SELECT y ORDER BY si aplican
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

### 🗺️ Mapa Mental Gráfico y Visual de la Arquitectura

Además del diagrama de secuencia, aquí tienes una representación visual del mapa mental del flujo de tu API REST:

![Mapa Mental de Arquitectura](file:///C:/Users/diego/.gemini/antigravity-ide/brain/98aee8dc-aac2-4169-9eb7-deaf1474b43c/api_architecture_mindmap_1781070929275.png)

---

## 📂 Descripción Detallada de cada Archivo y sus Conexiones

### 1. [index.php](file:///c:/wamp64/www/apirest-dinamica/index.php) (Punto de entrada principal)
*   **Propósito:** Recibe todas las solicitudes del servidor web (redireccionadas por el `.htaccess`). Inicializa la configuración de errores del sistema y arranca la API instanciando el despachador de rutas.
*   **Conexión de entrada:** Petición HTTP directa del cliente.
*   **Conexión de salida:** Llama a `controllers/routes.contoller.php`.

### 2. [routes.contoller.php](file:///c:/wamp64/www/apirest-dinamica/controllers/routes.contoller.php) (El despachador)
*   **Propósito:** Define la clase `routesController` cuyo método `index()` incluye el archivo de definición de enrutamiento del backend.
*   **Conexión de entrada:** Instanciado por `index.php`.
*   **Conexión de salida:** Incluye el archivo de rutas `routes/routes.php`.

### 3. [routes.php](file:///c:/wamp64/www/apirest-dinamica/routes/routes.php) (El semáforo de la API)
*   **Propósito:** Analiza la URI de la petición, determina a qué tabla se quiere acceder, y evalúa el método HTTP correspondiente (`GET`, `POST`, `PUT`, `DELETE`).
*   **Conexión de entrada:** Incluido por `routesController`.
*   **Conexión de salida:** Si es una petición `GET`, incluye el servicio específico en `routes/services/get.php`.

### 4. [get.php](file:///c:/wamp64/www/apirest-dinamica/routes/services/get.php) (El integrador del servicio GET)
*   **Propósito:** *[Cambio por IA]* Procesa específicamente las peticiones de lectura (`GET`). Realiza la extracción y saneamiento básico de los siguientes parámetros provenientes de la URL:
    *   **Tabla:** Extraída eliminando la query string (`explode('?', $routesArray[1])[0]`).
    *   `$select`: Columnas a consultar (obtenida de `$_GET['select']`, por defecto `*`).
    *   `$orderBy`: Columna por la cual ordenar (obtenida de `$_GET['orderBy']`, por defecto `null`).
    *   `$orderMode`: Dirección de ordenamiento (obtenida de `$_GET['orderMode']`, por defecto `null`).
*   **Flujo de Control:**
    *   Evalúa si el cliente envió parámetros de filtro en la URL (`linkTo` y `equalTo`).
    *   **Con Filtro:** Llama a `$response->getDataFilter($table, $select, $_GET["linkTo"], $_GET["equalTo"], $orderBy, $orderMode)`.
    *   **Sin Filtro:** Llama a `$response->getData($table, $select, $orderBy, $orderMode)`.
*   **Conexión de entrada:** Incluido por `routes/routes.php`.
*   **Conexión de salida:** Instancia `GetController` e invoca sus métodos.

### 5. [get.controller.php](file:///c:/wamp64/www/apirest-dinamica/controllers/get.controller.php) (El cerebro de las peticiones GET)
*   **Propósito:** *[Cambio por IA]* Valida la estructura de la consulta entrante y decide a qué método del modelo invocar. Cuenta con:
    *   `getData()`: Intermediario para consultas sin condiciones de filtrado.
    *   `getDataFilter()`: Intermediario para consultas con filtros (WHERE).
    *   `fncResponse()`: Formatea los datos de salida a JSON, calcula el número de resultados (`total`) y responde con los códigos de estado HTTP correctos (200 en éxito, 404 si la búsqueda no arroja registros).
*   **Conexión de entrada:** Instanciado y ejecutado desde `routes/services/get.php`.
*   **Conexión de salida:** Llama a `GetModel::getData()` o `GetModel::getDataFilter()`.

### 6. [get.model.php](file:///c:/wamp64/www/apirest-dinamica/models/get.model.php) (El constructor dinámico de consultas SQL)
*   **Propósito:** *[Cambio por IA]* Ejecuta el acceso directo a la base de datos armando las consultas SQL en tiempo de ejecución.
*   **Flujo Interno:**
    *   **Peticiones sin filtro (`getData`):** Construye la consulta uniendo columnas (`$select`), tabla (`$table`) y la ordenación si `$orderBy` y `$orderMode` están definidos.
    *   **Peticiones con filtro (`getDataFilter`):**
        1.  Divide las columnas del filtro (`$linkTo`) por comas y los valores de coincidencia (`$equalTo`) por guiones bajos.
        2.  Construye dinámicamente la cláusula SQL `WHERE` encadenando `AND columna = :columna` para cada filtro adicional.
        3.  Agrega la cláusula de ordenamiento `ORDER BY` si corresponde.
        4.  Prepara la consulta SQL mediante PDO (`prepare($sql)`).
        5.  Vincula dinámicamente los valores usando `$stmt->bindParam()` con el tipo `PDO::PARAM_STR`, previniendo inyecciones SQL en los valores filtrados.
        6.  Retorna los registros en formato de clases genéricas (`PDO::FETCH_CLASS`).
*   **Conexión de entrada:** Requerido y llamado por `GetController`.
*   **Conexión de salida:** Ejecuta la consulta en la base de datos a través del canal abierto por `Connection::connectDataBase()`.

### 7. [connection.php](file:///c:/wamp64/www/apirest-dinamica/models/connection.php) (El conector de datos)
*   **Propósito:** Establece la configuración de conexión de red local y las credenciales (base de datos, usuario, contraseña) y retorna una instancia activa tipo **PDO**.
*   **Conexión de entrada:** Utilizado estáticamente por `GetModel`.
*   **Conexión de salida:** Abre el socket o canal directo a la instancia local de MySQL en WampServer.

---

## 🛡️ Alerta de Ciberseguridad y Buenas Prácticas (Enfoque OWASP)

> [!WARNING]
> ### 🚨 Riesgo de Inyección SQL de Identificadores (Tablas, Columnas y Cláusulas)
> 
> En los métodos `getData` y `getDataFilter` del archivo `models/get.model.php`, observamos las siguientes instrucciones:
> ```php
> $sql = "SELECT $select FROM $table";
> // ...
> $sql .= " ORDER BY $orderBy $orderMode";
> ```
> Aunque la cláusula `WHERE` utiliza sentencias preparadas y vinculación segura (`bindParam`) para los **valores** de búsqueda, **los identificadores de SQL (nombres de tablas, nombres de columnas en SELECT, columnas en ORDER BY y la dirección ASC/DESC) NO se pueden parametrizar en PDO** con marcadores de posición.
> 
> Al concatenar `$table`, `$select`, `$orderBy` y `$orderMode` directamente desde los parámetros `$_GET` de la URL, la API se expone a ataques de **Inyección SQL de Identificadores de Base de Datos**.
> 
> **Cómo mitigarlo (Buenas Prácticas):**
> 1.  **Lista Blanca (Whitelist):** Antes de enviar estos parámetros al modelo, el controlador (`GetController`) debe validar que `$table` y las columnas especificadas en `$select` y `$orderBy` existan dentro de una lista de tablas/columnas autorizadas.
> 2.  **Saneamiento de Ordenamiento:** Limitar estrictamente el parámetro `$orderMode` a un valor coincidente con `ASC` o `DESC` (insensible a mayúsculas), y rechazar cualquier otro string.
> 3.  **Metadatos de la Base de Datos:** Consultar dinámicamente el esquema de la base de datos (`INFORMATION_SCHEMA.COLUMNS`) para verificar si la columna y tabla solicitadas existen en el servidor antes de incorporarlas en la cadena SQL.

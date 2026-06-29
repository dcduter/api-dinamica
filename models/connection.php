<?php
require_once 'get.model.php';
require_once 'vendor/autoload.php';

// se use el componente jwt
use Firebase\JWT\JWT;

/**
 * Clase Connection
 *
 * [Cambio por IA]
 * Se encarga de proveer las credenciales y el objeto de conexión de base de datos
 * utilizando la extensión segura PDO de PHP.
 *
 * @category Database
 * @package  Models
 */
class Connection
{
    /**
     * Variable para rastrear si ya se cargaron las variables de entorno.
     * [Cambio por IA]
     */
    static private $envLoaded = false;

    /**
     * Carga de manera nativa y segura las variables del archivo .env a $_ENV, $_SERVER y putenv.
     * [Cambio por IA]
     */
    static public function loadEnv()
    {
        if (self::$envLoaded) {
            return;
        }
        $path = dirname(__DIR__) . '/.env';
        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    $value = trim($value, '"\'');
                    if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                        putenv("{$name}={$value}");
                        $_ENV[$name] = $value;
                        $_SERVER[$name] = $value;
                    }
                }
            }
        }
        self::$envLoaded = true;
    }

    /**
     * INFORMACIÓN DE LA BASE DE DATOS
     *
     * [Cambio por IA]
     * Método estático que retorna la configuración de conexión de la BD en un array asociativo.
     * Lee de forma segura desde las variables de entorno cargadas.
     *
     * @return array Arreglo con claves 'database', 'user' y 'pass'.
     */
    static public function infoDataBase()
    {
        self::loadEnv();

        $infoDB = array(
            'database' => getenv('DB_NAME') ?: 'database-1',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''
        );

        return $infoDB;
    }

    /**
     * CONEXIÓN CON LA BASE DE DATOS
     *
     * [Cambio por IA]
     * Establece una conexión persistente a la base de datos usando PDO.
     * Se convierte en método estático (static) para evitar la instanciación innecesaria de la clase Connection,
     * optimizando el uso de memoria del servidor, y se corrige la ortografía a 'connectDataBase'.
     *
     * @return PDO Retorna el objeto de conexión PDO.
     * @throws PDOException Si la conexión a la base de datos falla.
     */
    static public function connectDataBase()
    {
        try {
            $link = new PDO(
                // se envia el nombre de la base de datos
                /*  "mysql:host=localhost;dbname=" .
                Connection::infoDataBase()["database"]
                - mysql:: Indica que el controlador que se debe usar es el de MySQL.
                - host=localhost: Especifica que la base de datos está en el mismo
                servidor donde corre el código.
                - dbname=: Aquí es donde se indica el nombre de la base de datos.
                - . Connection::infoDataBase()["database"]: El punto . en PHP sirve
                para concatenar (unir) strings. Aquí está llamando a un método
                estático llamado infoDataBase() de la clase Connection, el cual
                probablemente devuelve un arreglo con la configuración, y de ahí
                extrae el valor de la llave "database".

                B. El Usuario - El segundo argumento:

                Connection::infoDataBase()["user"]
                - Extrae el nombre de usuario de la base de datos desde la misma clase
                de configuración.

                C. La Contraseña - El tercer argumento:

                Connection::infoDataBase()["pass"]
                - Extrae la contraseña de la base de datos desde la misma clase de
                configuración.*/
                'mysql:host=localhost;dbname=' . Connection::infoDataBase()['database'],
                // se envia el usuario
                Connection::infoDataBase()['user'],
                // se envia la contraseña
                Connection::infoDataBase()['pass']
            );

            // Habilitar el reporte de errores por excepciones en PDO (Práctica OWASP recomendada)
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $link->exec('set names utf8');
        } catch (PDOException $e) {
            // En producción se debe registrar en un log y mostrar un mensaje genérico
            error_log('Error de Conexión BD: ' . $e->getMessage());
            die('Error de conexión seguro: No se pudo conectar a la Base de Datos.');
        }

        return $link;
    }

    /* ======== VALIDAR LA EXISTENCIA DE UNA TABLA EN LA BD */

    // Nota: este metodo se debe llamar en cada solicitud en el modelo para que valide si existe la tabla y si no existe da status 404
    static public function getColumnsData($table, $columns)
    {
        // se almacena el nombre de la base de datos en una variable
        $database = Connection::infoDataBase()['database'];

        // [Cambio por IA] Corrección de Inyección SQL: Se usa una consulta preparada en lugar de query() con interpolación directa.
        $link = Connection::connectDataBase();
        $stmt = $link->prepare("SELECT COLUMN_NAME AS item FROM information_schema.columns WHERE table_schema = :database AND table_name = :table");
        $stmt->execute([
            ':database' => $database,
            ':table' => $table
        ]);
        $validate = $stmt->fetchAll(PDO::FETCH_OBJ);  // el OBJ es para obtener la consulta en un objeto

        // validar que la tabla existe
        if (!$validate) {
            return null;
        } else {
            // validar la seleccion de columnas globales con el parametro *
            // [Cambio por IA] Se verifica que el array no esté vacío antes de acceder al índice 0
            // para evitar la advertencia de 'Undefined array key 0' cuando se pasan arrays vacíos.
            if (!empty($columns) && $columns[0] == '*') {
                // se elimina el primar indice del array y se almacena en la variable
                array_shift($columns);
            }

            // validar existencia de columnas
            $sum = 0;

            foreach ($validate as $key => $value) {
                $sum += in_array($value->item, $columns);
            }
            return $sum == count($columns) ? $validate : null;
        }
    }

    /*==================================
    generar token de autenticaion
    =================================== */

    // con el id y el email
    static public function jwt($id, $email)
    {
        $time = time();  // devuelve el tiempo actual en segundos

        $token = array(
            'iat' => $time,
            'exp' => $time + (60 * 60 * 24),  // tiempo final del token es de 1 dia
            'data' => [
                'id' => $id,
                'email' => $email
            ]
        );

        return $token;  // se entrega el token generado para que el controller lo envie al cliente
    }

    /* =====================================================
    Validar el token de seguridad
    ================================================= */

    static public function tokenValidate($token, $table, $suffix)
    {
        /* ===========================================
            Traemos el usario de acuerdo al token
          ========================================= */
        // se usa getDataFilter para buscar el usuario por el token
        $user = GetModel::getDataFilter($table, 'token_exp_'.$suffix, 'token_'.$suffix, $token, null, null, null, null);

        // si $user no viene vacio se valida el token
        if(!empty($user)){

            /* ================================
                validamos que el token no haya expirado
            ================================= */

            // si el tiempo actual es menor al tiempo de expiración del token
            if(time() < $user[0]->{'token_exp_'.$suffix}){
                return "ok";
            }else{
                return "expired";
            }


        }else{
            return "no autorizado";
        }

    }

    /* ======================================
    APIKEY
    ======================================== */

    static public function apiKey(){
       self::loadEnv();
       return getenv('API_KEY') ?: "api_XhoKafOhvrBeoiqkc2rxilhAOxd1tTJB";
    }

    /* ==============================
      Acceso publico
      ================================= */

      static public function publicAccess(){


        // tablas de acceso publico solo courses 
       $tables = ["courses"];

       return $tables;

      }
}

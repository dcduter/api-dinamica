<?php

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
     * INFORMACIÓN DE LA BASE DE DATOS
     * 
     * [Cambio por IA]
     * Método estático que retorna la configuración de conexión de la BD en un array asociativo.
     * 
     * @return array Arreglo con claves 'database', 'user' y 'pass'.
     */
    static public function infoDataBase()
    { // se usa static para que el metodo pueda ser llamado sin instanciar la clase 

        $infoDB = array(
            "database" => "database-1",
            "user" => "root",
            "pass" => ""
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
                "mysql:host=localhost;dbname=" . Connection::infoDataBase()["database"],

                // se envia el usuario
                Connection::infoDataBase()["user"],

                // se envia la contraseña
                Connection::infoDataBase()["pass"]
            );

            // Habilitar el reporte de errores por excepciones en PDO (Práctica OWASP recomendada)
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $link->exec("set names utf8");

        } catch (PDOException $e) {
            // En producción se debe registrar en un log y mostrar un mensaje genérico
            error_log("Error de Conexión BD: " . $e->getMessage());
            die("Error de conexión seguro: No se pudo conectar a la Base de Datos.");
        }

        return $link;



    }

    /* ======== VALIDAR LA EXISTENCIA DE UNA TABLA EN LA BD */
    // Nota: este metodo se debe llamar en cado solicitud en el modelo para que valide si existe la tabla y si no existe da status 404
    static public function getColumnsData($table, $columns)
    {

        //se almacena el nombre de la base de datos en una variable
        $database = Connection::infoDataBase()["database"];


        // teniendo la conexion se pasa en el siguente return para que compare el nombre de las tablas con la que se envia
        $validate = Connection::connectDataBase()
            // se retorna la seleccion de todas la tablas de la base
            ->query("SELECT COLUMN_NAME AS item FROM information_schema.columns WHERE table_schema = '$database' AND table_name = '$table'")
            ->fetchAll(PDO::FETCH_OBJ); // el OBJ es para obtener la consulta en un objeto

        // validar que la tabla existe 
        if (!$validate) {
            return null;
        } else {

            // validar la seleccion de columnas globales con el parametro *
            // [Cambio por IA] Se verifica que el array no esté vacío antes de acceder al índice 0
            // para evitar la advertencia de 'Undefined array key 0' cuando se pasan arrays vacíos.
            if (!empty($columns) && $columns[0] == "*"){

              // se elimina el primar indice del array y se almacena en la variable
              array_shift($columns);
            }

            

            //validar existencia de columnas
            $sum = 0;

            foreach ($validate as $key => $value) {

                $sum += in_array($value->item, $columns);
            }
            return $sum == count($columns) ? $validate : null;
        }
    }
}


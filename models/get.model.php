<?php

// conecta a la base de datos
require_once "connection.php";

/**
 * Clase GetModel
 * 
 * [Cambio por IA]
 * Modelo encargado de realizar las consultas directas (lectura / GET) en la base de datos
 * utilizando la conexión de base de datos activa por medio de PDO.
 * 
 * @category Model
 * @package  Models
 */
class GetModel
{

    /**
     * Obtiene todos los registros de una tabla de base de datos específica de forma dinámica.
     * 
     * [Cambio por IA]
     * Se cambió la llamada al método de conexión corregido a 'Connection::connectDataBase()'.
     * Adicionalmente, se documentó para alertar que $table debe sanitizarse externamente
     * para mitigar inyecciones SQL de identificadores de tablas.
     * 
     * @param string $table Nombre de la tabla a consultar.
     * @return array Conjunto de registros estructurados como objetos de clase (PDO::FETCH_CLASS).
     */

    /* ========= peticiones sin filtro ========= */

    static public function getData($table, $select, $orderBy, $orderMode, $startAt, $endAt)
    {
        // Validacion si existe la tabla y las columnas
        $selectArray = explode(",", $select);
        if (!Connection::getColumnsData($table, $selectArray)) {
            return null;
        }




        //la consulta se prepara de forma dinamica con $select que viene desde el controlador y este desde get.php
        // SIN ORDENAR NI LIMITAR DATOS
        $sql = "SELECT $select FROM $table";

        // si orderBy y orderMode no son nulos se añade a la consulta sql, starAt y endAt en null para no limitar los datos, solo ordenarlos
        if ($orderBy != null && $orderMode != null && $startAt == null && $endAt == null) {
            $sql .= " ORDER BY $orderBy $orderMode";
        }

        // ordenar y limitar datos
        if ($orderBy != null && $orderMode != null && $startAt != null && $endAt != null) {
            $sql .= " ORDER BY $orderBy $orderMode limit $startAt, $endAt";
        }

        // limitar los datos sin ordenarlos
        if ($orderBy == null && $orderMode == null && $startAt != null && $endAt != null) {
            $sql .= " limit $startAt, $endAt";
        }

        // preparación de la sentencia sql
        try {
            $stmt = Connection::connectDataBase()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS);
        } catch (PDOException $e) {
            // [Cambio por IA] Capturamos el error para no mostrar detalles técnicos al usuario.
            error_log("Error SQL en getData: " . $e->getMessage());
            return null;
        }
    }

    /* ========= peticiones con filtro ========= */

    static public function getDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {
        // [Cambio por IA] Validacion si existe la tabla y las columnas
        $selectArray = explode(",", $select);
        $linkToArray = explode(",", $linkTo);

        // se pone en un array para que no haga una matris completa
        foreach ($linkToArray as $key => $value) {
            array_push($selectArray, $value);
        }

        // quita los elementos duplicados
        $selectArray = array_unique($selectArray);


        if (!Connection::getColumnsData($table, $selectArray)) {
            return null;
        }

        // Transformamos los parámetros de cadena a arreglos para procesarlos individualmente.
        // Ejemplo: "col1,col2" -> ["col1", "col2"]
        //  $linkToArray = explode(",", $linkTo);

        // Transformamos los valores de filtro de cadena a arreglo.
        // Se separan por "_" según la convención establecida para los valores de filtro.
        $equalToArray = explode("_", $equalTo);

        $linkToText = ""; // Variable para ir construyendo dinámicamente la parte SQL del WHERE

        // Construcción dinámica de la cadena WHERE:
        // Si hay más de un filtro, necesitamos agregar 'AND' entre ellos.
        if (count($linkToArray) > 1) {

            foreach ($linkToArray as $key => $value) {
                // El primer elemento se maneja directamente en el SQL, los siguientes llevan 'AND'
                if ($key > 0) {
                    // Construimos la parte SQL: "AND columna = :columna"
                    // Usamos marcadores de posición (ej: :nombre_columna) para seguridad.
                    $linkToText .= " AND " . $value . "= :" . $value . "";
                }
            }
        }

        // Armamos la consulta SQL final:
        // SELECT columna FROM tabla WHERE primera_columna = :primera_columna AND ...otros_filtros
        // SIN ORDENAR NI LIMITAR DATOS
        $sql = "SELECT $select FROM $table WHERE $linkToArray[0] = :$linkToArray[0] $linkToText";

        // ORDENAR DATOS SIN LIMITES
        if ($orderBy != null && $orderMode != null && $startAt == null && $endAt == null) {
            $sql .= " ORDER BY $orderBy $orderMode";
        }

        // ORDENAR Y LIMITAR DATOS
        if ($orderBy != null && $orderMode != null && $startAt != null && $endAt != null) {
            $sql .= " ORDER BY $orderBy $orderMode limit $startAt, $endAt";
        }

        // LIMITAR DATOS SIN ORDENAR
        if ($orderBy == null && $orderMode == null && $startAt != null && $endAt != null) {
            $sql .= " limit $startAt, $endAt";
        }

        // Preparamos la sentencia SQL con PDO. Esto previene Inyección SQL.
        $stmt = Connection::connectDataBase()->prepare($sql);

        // Vinculación (Binding) de los parámetros:
        // Recorremos el arreglo de columnas para asignar el valor correspondiente a cada marcador de posición.
        foreach ($linkToArray as $key => $value) {
            // bindParam sustituye el marcador (ej: :columna) por el valor real del filtro.
            // PDO::PARAM_STR asegura que el dato se trate como cadena.
            $stmt->bindParam(":" . $value, $equalToArray[$key], PDO::PARAM_STR);
        }

        // Ejecutamos la consulta ya armada y con los valores vinculados.
        try {
            $stmt->execute();
            // Obtenemos los resultados como objetos y los retornamos al controlador.
            return $stmt->fetchAll(PDO::FETCH_CLASS);
        } catch (PDOException $e) {
            // [Cambio por IA] Capturamos el error para no mostrar detalles técnicos al usuario.
            error_log("Error SQL en getDataFilter: " . $e->getMessage());
            return null;
        }
    }

    /* ========= peticiones GET sin filtro entre tablas relacionadas ========= */

    static public function getRelData($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt)
    {
        // [Cambio por IA] Validacion si existen las tablas y las columnas de la relacion
        $selectArray = explode(",", $select);
        if (!self::validateFieldsRelation($rel, $selectArray)) {
            return null;
        }

        // reparamos por "," los datos de rel y type
        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innerJoinText = ""; // para que sea usado por el if del array



        if (count($relArray) > 1) {

            foreach ($relArray as $key => $value) {
                // El primer elemento se maneja directamente en el SQL, los siguientes llevan 'AND'
                if ($key > 0) {
                    // Construimos la parte SQL: "AND columna = :columna"
                    // Usamos marcadores de posición (ej: :nombre_columna) para seguridad.
                    $innerJoinText .= "inner join " . $value . " on " . $relArray[0] . ".id_" . $typeArray[$key] . "_" . $typeArray[0] . " = " . $value . ".id_" . $typeArray[$key] . " ";
                }
            }
        }

        //la consulta se prepara de forma dinamica con $select que viene desde el controlador y este desde get.php
        // SIN ORDENAR NI LIMITAR DATOS
        $sql = "SELECT $select FROM $relArray[0] $innerJoinText";

        // si orderBy y orderMode no son nulos se añade a la consulta sql, starAt y endAt en null para no limitar los datos, solo ordenarlos
        if ($orderBy != null && $orderMode != null && $startAt == null && $endAt == null) {
            $sql .= " ORDER BY $orderBy $orderMode";
        }

        // ordenar y limitar datos
        if ($orderBy != null && $orderMode != null && $startAt != null && $endAt != null) {
            $sql .= " ORDER BY $orderBy $orderMode LIMIT $startAt, $endAt";
        }

        // limitar los datos sin ordenarlos
        if ($orderBy == null && $orderMode == null && $startAt != null && $endAt != null) {
            $sql .= " LIMIT $startAt, $endAt";
        }

        // preparación de la sentencia sql
        try {
            $stmt = Connection::connectDataBase()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS);
        } catch (PDOException $e) {
            // [Cambio por IA] Capturamos el error para no mostrar detalles técnicos al usuario.
            error_log("Error SQL en getRelData: " . $e->getMessage());
            return null;
        }
    }

    /* ========= peticiones GET con filtro entre tablas relacionadas ========= */
    static public function getRelDataFilter($rel, $type, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {
        // [Cambio por IA] Validacion si existen las tablas y las columnas de la relacion
        $selectArray = explode(",", $select);
        $linkToArray = explode(",", $linkTo);
        foreach ($linkToArray as $value) {
            array_push($selectArray, $value);
        }
        $selectArray = array_unique($selectArray);

        if (!self::validateFieldsRelation($rel, $selectArray)) {
            return null;
        }

        // ----- Orgarnizamos lo filtros -----

        $linkToArray = explode(",", $linkTo); // separa los campos por coma

        $equalToArray = explode("_", $equalTo); // separa los valores por guion

        $linkToText = ""; // Variable para ir construyendo dinámicamente la parte SQL del WHERE

        // Construcción dinámica de la cadena WHERE:
        // Si hay más de un filtro, necesitamos agregar 'AND' entre ellos.
        if (count($linkToArray) > 1) {

            foreach ($linkToArray as $key => $value) {
                // El primer elemento se maneja directamente en el SQL, los siguientes llevan 'AND'
                if ($key > 0) {
                    // Construimos la parte SQL: "AND columna = :columna"
                    // Usamos marcadores de posición (ej: :nombre_columna) para seguridad.
                    $linkToText .= " AND " . $value . "= :" . $value . "";
                }
            }
        }


        // ----- Organizamos las relaciones -----

        // reparamos por "," los datos de rel y type
        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innerJoinText = ""; // para que sea usado por el if del array



        if (count($relArray) > 1) {

            foreach ($relArray as $key => $value) {
                // El primer elemento se maneja directamente en el SQL, los siguientes llevan 'AND'
                if ($key > 0) {
                    // Construimos la parte SQL: "AND columna = :columna"
                    // Usamos marcadores de posición (ej: :nombre_columna) para seguridad.
                    $innerJoinText .= "inner join " . $value . " on " . $relArray[0] . ".id_" . $typeArray[$key] . "_" . $typeArray[0] . " = " . $value . ".id_" . $typeArray[$key] . " ";
                }
            }


            //la consulta se prepara de forma dinamica con $select que viene desde el controlador y este desde get.php
            // --- SIN ORDENAR NI LIMITAR DATOS ---
            $sql = "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkToArray[0] = :$linkToArray[0] $linkToText";

            // si orderBy y orderMode no son nulos se añade a la consulta sql, starAt y endAt en null para no limitar los datos, solo ordenarlos
            if ($orderBy != null && $orderMode != null && $startAt == null && $endAt == null) {
                $sql .= " ORDER BY $orderBy $orderMode";
            }

            // ordenar y limitar datos
            if ($orderBy != null && $orderMode != null && $startAt != null && $endAt != null) {
                $sql .= " ORDER BY $orderBy $orderMode LIMIT $startAt, $endAt";
            }

            // limitar los datos sin ordenarlos
            if ($orderBy == null && $orderMode == null && $startAt != null && $endAt != null) {
                $sql .= " LIMIT $startAt, $endAt";
            }

            // preparación de la sentencia sql
            try {
                $stmt = Connection::connectDataBase()->prepare($sql);

                foreach ($linkToArray as $key => $value) {
                    // bindParam sustituye el marcador (ej: :columna) por el valor real del filtro.
                    // PDO::PARAM_STR asegura que el dato se trate como cadena.
                    $stmt->bindParam(":" . $value, $equalToArray[$key], PDO::PARAM_STR);
                }

                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_CLASS);
            } catch (PDOException $e) {
                // [Cambio por IA] Capturamos el error para no mostrar detalles técnicos al usuario.
                error_log("Error SQL en getRelDataFilter: " . $e->getMessage());
                return null;
            }

        } else {
            return null;
        }
    }

    /* ============================== Peticiones GET para el buscador sin relaciones ======================= */

    static public function getDataSearch($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
    {
        // [Cambio por IA] Validacion si existe la tabla y las columnas
        $selectArray = explode(",", $select);
        $linkToArray = explode(",", $linkTo);
        foreach ($linkToArray as $value) {
            array_push($selectArray, $value);
        }
        $selectArray = array_unique($selectArray);

        if (!Connection::getColumnsData($table, $selectArray)) {
            return null;
        }

        // Transformamos los parámetros de cadena a arreglos para procesarlos individualmente.
        // Ejemplo: "col1,col2" -> ["col1", "col2"]
        $linkToArray = explode(",", $linkTo);

        // Transformamos los valores de filtro de cadena a arreglo.
        // Se separan por "_" según la convención establecida para los valores de filtro.
        $searchToArray = explode("_", $search);

        $linkToText = ""; // Variable para ir construyendo dinámicamente la parte SQL del WHERE

        // Construcción dinámica de la cadena WHERE:
        // Si hay más de un filtro, necesitamos agregar 'AND' entre ellos.
        if (count($linkToArray) > 1) {

            foreach ($linkToArray as $key => $value) {
                // El primer elemento se maneja directamente en el SQL, los siguientes llevan 'AND'
                if ($key > 0) {
                    // Construimos la parte SQL: "AND columna = :columna"
                    // Usamos marcadores de posición (ej: :nombre_columna) para seguridad.
                    $linkToText .= "AND " . $value . " = :" . $value . " ";
                }
            }
        }

        //la consulta se prepara de forma dinamica con $select que viene desde el controlador y este desde get.php
        // SIN ORDENAR NI LIMITAR DATOS
        $sql = "SELECT $select FROM $table WHERE $linkToArray[0] LIKE '%$searchToArray[0]%' $linkToText";

        // si orderBy y orderMode no son nulos se añade a la consulta sql, starAt y endAt en null para no limitar los datos, solo ordenarlos
        if ($orderBy != null && $orderMode != null && $startAt == null && $endAt == null) {
            $sql .= " ORDER BY $orderBy $orderMode";
        }

        // ordenar y limitar datos
        if ($orderBy != null && $orderMode != null && $startAt != null && $endAt != null) {
            $sql .= " ORDER BY $orderBy $orderMode limit $startAt, $endAt";
        }

        // limitar los datos sin ordenarlos
        if ($orderBy == null && $orderMode == null && $startAt != null && $endAt != null) {
            $sql .= " limit $startAt, $endAt";
        }

        // preparación de la sentencia sql
        try {
            $stmt = Connection::connectDataBase()->prepare($sql);

            // se hace el foreach para arme la 
            foreach ($linkToArray as $key => $value) {

                // pone en un if para que no tenga encuenta el indice 0, para evitar errores de logica
                if ($key > 0) {
                    $stmt->bindParam(":" . $value, $searchToArray[$key], PDO::PARAM_STR);
                }
            }

            // Construimos el patrón LIKE con wildcard (%) al inicio y al final.
            // Esto permite buscar el término en cualquier parte de la cadena.
            // $searchParam = "%" . $search . "%";

            // Vinculamos el valor a la variable de marcador :search.
            // PDO::PARAM_STR asegura que se maneje como texto.
            //  $stmt->bindParam(":search", $searchParam, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_CLASS);
        } catch (PDOException $e) {
            // [Cambio por IA] Capturamos el error para no mostrar detalles técnicos al usuario.
            error_log("Error SQL en getDataSearch: " . $e->getMessage());
            return null;
        }

    }

    /* ========= Peticiones GET para el buscador entre tablas relacionadas ========= */
    static public function getRelDataSearch($rel, $type, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
    {
        // [Cambio por IA] Validacion si existen las tablas y las columnas de la relacion
        $selectArray = explode(",", $select);
        $linkToArray = explode(",", $linkTo);
        foreach ($linkToArray as $value) {
            array_push($selectArray, $value);
        }
        $selectArray = array_unique($selectArray);

        if (!self::validateFieldsRelation($rel, $selectArray)) {
            return null;
        }

        // ----- Orgarnizamos lo filtros -----

        $linkToArray = explode(",", $linkTo); // separa los campos por coma

        $searchToArray = explode("_", $search); // separa los valores por guion

        $linkToText = ""; // Variable para ir construyendo dinámicamente la parte SQL del WHERE

        // Construcción dinámica de la cadena WHERE:
        // Si hay más de un filtro, necesitamos agregar 'AND' entre ellos.
        if (count($linkToArray) > 1) {

            foreach ($linkToArray as $key => $value) {
                // El primer elemento se maneja directamente en el SQL, los siguientes llevan 'AND'
                if ($key > 0) {
                    // Construimos la parte SQL: "AND columna = :columna"
                    // Usamos marcadores de posición (ej: :nombre_columna) para seguridad.
                    $linkToText .= " AND " . $value . "= :" . $value . "";
                }
            }
        }


        // ----- Organizamos las relaciones -----

        // reparamos por "," los datos de rel y type
        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innerJoinText = ""; // para que sea usado por el if del array



        if (count($relArray) > 1) {

            foreach ($relArray as $key => $value) {
                // El primer elemento se maneja directamente en el SQL, los siguientes llevan 'AND'
                if ($key > 0) {
                    // Construimos la parte SQL: "AND columna = :columna"
                    // Usamos marcadores de posición (ej: :nombre_columna) para seguridad.
                    $innerJoinText .= "inner join " . $value . " on " . $relArray[0] . ".id_" . $typeArray[$key] . "_" . $typeArray[0] . " = " . $value . ".id_" . $typeArray[$key] . " ";
                }
            }


            //la consulta se prepara de forma dinamica con $select que viene desde el controlador y este desde get.php
            // --- SIN ORDENAR NI LIMITAR DATOS ---
            $sql = "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkToArray[0] LIKE '%$searchToArray[0]%' $linkToText";

            // si orderBy y orderMode no son nulos se añade a la consulta sql, starAt y endAt en null para no limitar los datos, solo ordenarlos
            if ($orderBy != null && $orderMode != null && $startAt == null && $endAt == null) {
                $sql .= " ORDER BY $orderBy $orderMode";
            }

            // ordenar y limitar datos
            if ($orderBy != null && $orderMode != null && $startAt != null && $endAt != null) {
                $sql .= " ORDER BY $orderBy $orderMode LIMIT $startAt, $endAt";
            }

            // limitar los datos sin ordenarlos
            if ($orderBy == null && $orderMode == null && $startAt != null && $endAt != null) {
                $sql .= " LIMIT $startAt, $endAt";
            }

            // preparación de la sentencia sql
            try {
                $stmt = Connection::connectDataBase()->prepare($sql);

                foreach ($linkToArray as $key => $value) {
                    // bindParam sustituye el marcador (ej: :columna) por el valor real del filtro.
                    // PDO::PARAM_STR asegura que el dato se trate como cadena.
                    if ($key > 0) {
                        // [Cambio por IA] Se corrigió la variable $searchArray a $searchToArray
                        $stmt->bindParam(":" . $value, $searchToArray[$key], PDO::PARAM_STR);
                    }
                }

                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_CLASS);
            } catch (PDOException $e) {
                // [Cambio por IA] Capturamos el error para no mostrar detalles técnicos al usuario.
                error_log("Error SQL en getRelDataSearch: " . $e->getMessage());
                return null;
            }

        } else {
            return null;
        }
    }

    /* ============= modelo peticiones GET para selecion de rangos ============= */
    static public function getDataRange($table, $select, $linkTo, $between1, $between2, $orderBy, $orderMode, $startAt, $endAt, $filterTo, $inTo)
    {
        // [Cambio por IA] Validacion si existe la tabla y las columnas
        $selectArray = explode(",", $select);
        $linkToArray = explode(",", $linkTo);
        foreach ($linkToArray as $value) {
            array_push($selectArray, $value);
        }
        if ($filterTo != null) {
            array_push($selectArray, $filterTo);
        }
        $selectArray = array_unique($selectArray);

        if (!Connection::getColumnsData($table, $selectArray)) {
            return null;
        }

        // $linkToArray = explode(",", $linkTo);
        // $between1Array = explode(",", $between1);
        // $between2Array = explode(",", $between2);

        // $linkToText = "";

        // if (count($linkToArray) > 1) {

        //     foreach ($linkToArray as $key => $value) {
        //         if ($key > 0) {
        //             $linkToText .= " AND " . $value . " = :" . $value . " ";
        //         }
        //     }
        // }
        $filter = "";
        if ($filterTo != null && $inTo != null) {

            $filter .= " AND $filterTo IN ($inTo)";

        }
        // -- sin ordenar ni limitar datos --
        $sql = "SELECT $select FROM $table WHERE $linkTo BETWEEN '$between1' AND '$between2' $filter";

        // -- ordenar datos sin limitar --
        if ($orderBy != null && $orderMode != null && $startAt == null && $endAt == null) {
            $sql .= " ORDER BY $orderBy $orderMode";
        }

        // -- ordenar y limitar datos --
        if ($orderBy != null && $orderMode != null && $startAt != null && $endAt != null) {
            $sql .= " ORDER BY $orderBy $orderMode LIMIT $startAt, $endAt";
        }

        // -- limitar datos sin ordenar --
        if ($orderBy == null && $orderMode == null && $startAt != null && $endAt != null) {
            $sql .= " LIMIT $startAt, $endAt";
        }

        $stmt = Connection::connectDataBase()->prepare($sql);

        // foreach ($linkToArray as $key => $value) {
        //     if ($key > 0) {
        //         $stmt->bindParam(":" . $value, $between1Array[$key], PDO::PARAM_STR);
        //     }
        // }

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS);
        } catch (PDOException $e) {
            // [Cambio por IA] Capturamos el error para no mostrar detalles técnicos al usuario.
            error_log("Error SQL en getDataRange: " . $e->getMessage());
            return null;
        }
    }
    /* ============= modelo peticiones GET para selecion de rangos con relaciones ============= */
    static public function getRelDataRange($rel, $type, $select, $linkTo, $between1, $between2, $orderBy, $orderMode, $startAt, $endAt, $filterTo, $inTo)
    {
        // [Cambio por IA] Validacion si existen las tablas y las columnas de la relacion
        $selectArray = explode(",", $select);
        $linkToArray = explode(",", $linkTo);
        foreach ($linkToArray as $value) {
            array_push($selectArray, $value);
        }
        if ($filterTo != null) {
            array_push($selectArray, $filterTo);
        }
        $selectArray = array_unique($selectArray);

        if (!self::validateFieldsRelation($rel, ["*"])) {
            return null;
        }

        $filter = "";
        // si el dirents de $filterTo y $inTo no es nulo se concatena los valores de $filterTo y $inTo para formar el where
        if ($filterTo != null && $inTo != null) {

            $filter .= " AND $filterTo IN ($inTo)";

        }

        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innerJoinText = "";

        if (count($relArray) > 1) {

            // se usa el foreach para 
            foreach ($relArray as $key => $value) {
                if ($key > 0) {
                    // se concatena los valores de $relArray y $typeArray para formar el inner join con la condicion de la llave foranea
                    $innerJoinText .= "inner join " . $value . " on " . $relArray[0] . ".id_" . $typeArray[$key] . "_" . $typeArray[0] . " = " . $value . ".id_" . $typeArray[$key] . " ";
                }
            }


            // -- sin ordenar ni limitar datos --
            $sql = "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkTo BETWEEN '$between1' AND '$between2' $filter";

            // -- ordenar datos sin limitar --
            if ($orderBy != null && $orderMode != null && $startAt == null && $endAt == null) {
                $sql .= " ORDER BY $orderBy $orderMode";
            }

            // -- ordenar y limitar datos --
            if ($orderBy != null && $orderMode != null && $startAt != null && $endAt != null) {
                $sql .= " ORDER BY $orderBy $orderMode LIMIT $startAt, $endAt";
            }

            // -- limitar datos sin ordenar --
            if ($orderBy == null && $orderMode == null && $startAt != null && $endAt != null) {
                $sql .= " LIMIT $startAt, $endAt";
            }

            $stmt = Connection::connectDataBase()->prepare($sql);

            // foreach ($linkToArray as $key => $value) {
            //     if ($key > 0) {
            //         $stmt->bindParam(":" . $value, $between1Array[$key], PDO::PARAM_STR);
            //     }
            // }

            try {
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_CLASS);
            } catch (PDOException $e) {
                // [Cambio por IA] Capturamos el error para no mostrar detalles técnicos al usuario.
                error_log("Error SQL en getRelDataRange: " . $e->getMessage());
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * [Cambio por IA]
     * Valida que las tablas y columnas de una relación existan en la base de datos.
     * 
     * @param string $rel Tablas de la relación separadas por comas.
     * @param array $fields Columnas a validar.
     * @return bool True si son válidas, false de lo contrario.
     */
    static private function validateFieldsRelation($rel, $fields)
    {
        $relArray = array_map('trim', explode(',', $rel));
        $tableColumns = [];

        // Obtener las columnas de cada tabla
        foreach ($relArray as $table) {
            // Pasamos un array vacío ya que Connection::getColumnsData ahora requiere array de columnas para contar/comparar
            $cols = Connection::getColumnsData($table, []);
            if (!$cols) {
                return false;
            }
            $tableColumns[$table] = array_map(function($c) {
                return $c->item;
            }, $cols);
        }

        // Filtrar y validar las columnas
        foreach ($fields as $field) {
            $field = trim($field);
            if ($field === '*' || empty($field)) {
                continue;
            }

            // Limpiar alias
            $parts = preg_split('/\s+/', $field);
            $fieldClean = $parts[0];

            if (strpos($fieldClean, '.') !== false) {
                list($tName, $cName) = explode('.', $fieldClean);
                $tName = trim($tName);
                $cName = trim($cName);

                if (!isset($tableColumns[$tName]) || !in_array($cName, $tableColumns[$tName])) {
                    return false;
                }
            } else {
                $found = false;
                foreach ($tableColumns as $tName => $cols) {
                    if (in_array($fieldClean, $cols)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    return false;
                }
            }
        }

        return true;
    }

} // se entrega al controlador
?>
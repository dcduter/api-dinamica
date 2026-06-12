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
class GetModel { 

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

    static public function getData($table, $select,$orderBy, $orderMode, $startAt, $endAt){
        
        //la consulta se prepara de forma dinamica con $select que viene desde el controlador y este desde get.php
        // SIN ORDENAR NI LIMITAR DATOS
        $sql = "SELECT $select FROM $table";

        // si orderBy y orderMode no son nulos se añade a la consulta sql, starAt y endAt en null para no limitar los datos, solo ordenarlos
        if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){ 
            $sql .= " ORDER BY $orderBy $orderMode";
        }

        // ordenar y limitar datos
           if($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){ 
            $sql .= " ORDER BY $orderBy $orderMode limit $startAt, $endAt";
        }

        // limitar los datos sin ordenarlos
           if($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){ 
            $sql .= " limit $startAt, $endAt";
        }
        
        // preparación de la sentencia sql
        $stmt = Connection::connectDataBase()->prepare($sql); 
        
        $stmt->execute(); 
        
        return $stmt->fetchAll(PDO::FETCH_CLASS); 
    }

    /* ========= peticiones con filtro ========= */

    static public function getDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt){

        // Transformamos los parámetros de cadena a arreglos para procesarlos individualmente.
        // Ejemplo: "col1,col2" -> ["col1", "col2"]
        $linkToArray = explode(",",$linkTo);
    
        // Transformamos los valores de filtro de cadena a arreglo.
        // Se separan por "_" según la convención establecida para los valores de filtro.
        $equalToArray = explode("_",$equalTo);

        $linkToText = ""; // Variable para ir construyendo dinámicamente la parte SQL del WHERE

        // Construcción dinámica de la cadena WHERE:
        // Si hay más de un filtro, necesitamos agregar 'AND' entre ellos.
        if (count($linkToArray) > 1){
            
            foreach ($linkToArray as $key => $value){
                // El primer elemento se maneja directamente en el SQL, los siguientes llevan 'AND'
                if($key > 0 ){
                    // Construimos la parte SQL: "AND columna = :columna"
                    // Usamos marcadores de posición (ej: :nombre_columna) para seguridad.
                    $linkToText .= " AND ".$value."= :".$value."";
                }
            }
        }
        
        // Armamos la consulta SQL final:
        // SELECT columna FROM tabla WHERE primera_columna = :primera_columna AND ...otros_filtros
        // SIN ORDENAR NI LIMITAR DATOS
        $sql = "SELECT $select FROM $table WHERE $linkToArray[0] = :$linkToArray[0] $linkToText";

        // ORDENAR DATOS SIN LIMITES
          if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){
            $sql .= " ORDER BY $orderBy $orderMode";
        }

        // ORDENAR Y LIMITAR DATOS
          if($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){
            $sql .= " ORDER BY $orderBy $orderMode limit $startAt, $endAt";
        }

        // LIMITAR DATOS SIN ORDENAR
          if($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){
            $sql .= " limit $startAt, $endAt";
        }
        
        // Preparamos la sentencia SQL con PDO. Esto previene Inyección SQL.
        $stmt = Connection::connectDataBase()->prepare($sql); 
        
        // Vinculación (Binding) de los parámetros:
        // Recorremos el arreglo de columnas para asignar el valor correspondiente a cada marcador de posición.
        foreach ($linkToArray as $key => $value){
            // bindParam sustituye el marcador (ej: :columna) por el valor real del filtro.
            // PDO::PARAM_STR asegura que el dato se trate como cadena.
            $stmt->bindParam(":".$value, $equalToArray[$key], PDO::PARAM_STR);
        }

        // Ejecutamos la consulta ya armada y con los valores vinculados.
        $stmt->execute(); 

        // Obtenemos los resultados como objetos y los retornamos al controlador.
        return $stmt->fetchAll(PDO::FETCH_CLASS); 
    }

     /* ========= peticiones GET sin filtro entre tablas relacionadas ========= */

    static public function getRelData($rel, $type, $select,$orderBy, $orderMode, $startAt, $endAt){

        // reparamos por "," los datos de rel y type
        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innerJoinText = ""; // para que sea usado por el if del array
        
        

        if (count($relArray ) > 1){
            
            foreach ($relArray as $key => $value){
                // El primer elemento se maneja directamente en el SQL, los siguientes llevan 'AND'
                if($key > 0 ){
                    // Construimos la parte SQL: "AND columna = :columna"
                    // Usamos marcadores de posición (ej: :nombre_columna) para seguridad.
                    $innerJoinText .= "inner join ".$value." on ".$relArray[0].".id_".$typeArray[$key]."_".$typeArray[0]." = ".$value.".id_".$typeArray[$key]." ";
                }
            }
        }
        
        //la consulta se prepara de forma dinamica con $select que viene desde el controlador y este desde get.php
        // SIN ORDENAR NI LIMITAR DATOS
        $sql = "SELECT $select FROM $relArray[0] $innerJoinText";

        // si orderBy y orderMode no son nulos se añade a la consulta sql, starAt y endAt en null para no limitar los datos, solo ordenarlos
        if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){ 
            $sql .= " ORDER BY $orderBy $orderMode";
        }

        // ordenar y limitar datos
           if($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){ 
            $sql .= " ORDER BY $orderBy $orderMode LIMIT $startAt, $endAt";
        }

        // limitar los datos sin ordenarlos
           if($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){ 
            $sql .= " LIMIT $startAt, $endAt";
        }
        
        // preparación de la sentencia sql
        $stmt = Connection::connectDataBase()->prepare($sql); 
        
        $stmt->execute(); 
        
        return $stmt->fetchAll(PDO::FETCH_CLASS); 
    }
}  // se entrega al controlador
?>
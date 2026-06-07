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
    static public function getData($table, $select){
        
        //la consulta se prepara de forma dinamica con $select que viene desde el controlador y este desde get.php
        $sql = "SELECT $select FROM $table";
        
        // preparación de la sentencia sql
        $stmt = Connection::connectDataBase()->prepare($sql); 
        
        $stmt->execute(); 
        
        return $stmt->fetchAll(PDO::FETCH_CLASS); 
    }
    
    /* ========= peticiones con filtro ========= */
    static public function getDataFilter($table, $select, $linkTo, $equalTo){
        
        //la consulta se prepara de forma dinamica con $select que viene desde el controlador y este desde get.php
        $sql = "SELECT $select FROM $table WHERE $linkTo = :$linkTo ";
        
        // preparación de la sentencia sql
        $stmt = Connection::connectDataBase()->prepare($sql); 
        
        //bindeo de parametros, el bindeo es para 
        $stmt->bindParam(":".$linkTo, $equalTo, PDO::PARAM_STR);

        // se ejecuta la sentencia sql
        $stmt->execute(); 

        // se obtienen todos los registros de la consulta
        return $stmt->fetchAll(PDO::FETCH_CLASS); 
    }
}  // se entrega al controlador
?>
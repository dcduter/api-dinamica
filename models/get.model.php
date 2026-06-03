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
    static public function getData($table){
        
        $sql = "SELECT * FROM $table";
        
        // preparación de la sentencia sql
        $stmt = Connection::connectDataBase()->prepare($sql); 
        
        $stmt->execute(); 
        
        return $stmt->fetchAll(PDO::FETCH_CLASS); 
    } 
}
?>
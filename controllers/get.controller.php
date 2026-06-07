<?php 

require_once "models/get.model.php";
/**
 * Clase GetController
 * 
 * [Cambio por IA]
 * Actúa como el intermediario (Controlador) para procesar solicitudes de lectura (GET).
 * Recibe peticiones desde el servicio de rutas (routes/services/get.php), realiza
 * validaciones primarias y delega la lectura a la base de datos a GetModel.
 * 
 * @category Controller
 * @package  Controllers
 */
class GetController{
     /**
     * Metodo para retornar los datos del modelo.
     * 
     * [Cambio por IA]
     * Recibe el nombre de la tabla, invoca al modelo de lectura de base de datos
     * y retorna el listado de registros obtenidos.
     * 
     * @param string $table Nombre de la tabla de la base de datos a consultar.
     * @return array|object Devuelve un conjunto de registros como array de objetos obtenidos de la BD.
     */

    /* ========= peticiones sin filtro ========= */
    static public function getData($table, $select){
        // se recive $select de la vista oseea get.php
      // se envia a get.model.php
      
      /* SE PIDE UN RESPUESTA DEL MODELO get.model.php */
      $response = GetModel::getData($table, $select);
      // lo devolvemos a routes/get.php
      // se retorna una nueva clase de getcontroller y se ejecuta el metodo fncResponse
      $return = new GetController();
      $return -> fncResponse($response); // se le pasa la respuesta que de el modelo
      
        
    }
     /* ========= peticiones con filtro ========= */
    static public function getDataFilter($table, $select, $linkTo, $equalTo){
        // se recive $select de la vista oseea get.php
      // se envia a get.model.php
      
      /* SE PIDE UN RESPUESTA DEL MODELO get.model.php */
      $response = GetModel::getDataFilter($table, $select, $linkTo, $equalTo);
      // lo devolvemos a el modelo y este a get.php
      // se retorna una nueva clase de getcontroller y se ejecuta el metodo fncResponse
      $return = new GetController();
      $return -> fncResponse($response); // se le pasa la respuesta que de el modelo
      
        
    }
    /* RESPUESTAS DEL CONTROLADOR */

    /**
     * Envía la respuesta JSON al cliente con el código de estado HTTP correspondiente.
     * 
     * [Cambio por IA]
     * Este método formatea la respuesta en una estructura JSON y establece el código
     * de estado HTTP según el resultado obtenido. Se corrigió un error de sintaxis
     * de llave faltante en la declaración del método.
     * 
     * @param array|object|null $response Datos obtenidos del modelo.
     * @return void
     */
    public function fncResponse($response) {

        if (!empty($response)) {
            $json = array(
                'status' => 200,
                'total' => count($response),
                'result' => $response
            );
        } else {
            $json = array(
                'status' => 404,
                'result' => 'no encontrado'
            );
        }

        echo json_encode($json, http_response_code($json["status"]));
    }

}
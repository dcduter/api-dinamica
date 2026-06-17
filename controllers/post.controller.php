<?php
// SE ENCARGA DE ENVIAR LOS DATO A EL MODELO

require_once "models/post.model.php";

class PostController{

    /* ===================================================
        Peticion POST para crear datos
       ===============================================*/
       

       // se crea una funcion statica 
       // se envia la data y la table
       static public function postData($data, $table){
       
        // se pide una respuesta del modelo
        $response = PostModel::postData($table, $data);
        
        // se retorna la respuesta al cliente
        $return = new PostController();

        // se usa la funcion de respuesta
        $return->fncResponse($response);
        return;

        
       }

       /* ========= RESPUESTAS DEL CONTROLADOR ========= */

    /**
     * Envía la respuesta JSON al cliente con el código de estado HTTP correspondiente.
     *
     * Este método formatea la respuesta en una estructura JSON y establece el código
     * de estado HTTP según el resultado obtenido.
     *
     * @param array|object|null $response Datos obtenidos del modelo.
     * @return void
     */
    public function fncResponse($response)
    {
        if (!empty($response)) {
            $json = array(
                'status' => 200,
                'total' => count($response),
                'result' => $response
            );
        } else {
            $json = array(
                'status' => 404,
                'result' => 'no encontrado',
                'method' => 'post'
            );
        }

        echo json_encode($json, http_response_code($json["status"]));
    }
}
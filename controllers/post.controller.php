<?php
// SE ENCARGA DE ENVIAR LOS DATO A EL MODELO

require_once "models/post.model.php";
require_once "models/connection.php";
require_once "models/get.model.php"; // para hace la solitud y saber si existe el usuario

require_once "vendor/autoload.php";
use Firebase\JWT\JWT;
require_once "models/put.model.php";


class PostController
{

    /* ===================================================
        Peticion POST para crear datos
       ===============================================*/


    // se crea una funcion statica 
    // se envia la data y la table
    static public function postData($data, $table)
    {

        // se pide una respuesta del modelo
        $response = PostModel::postData($table, $data);

        // se retorna la respuesta al cliente
        $return = new PostController();

        // se usa la funcion de respuesta
        $return->fncResponse($response, null, null);



    }
    /* ===================================================
        Peticion POST para registrar usuario
       ===============================================*/

    // se crea una funcion statica 
    // se envia la data y la table
    static public function postRegister($table, $data, $suffix)
    {

        // se pregunta si la contraseña no viena null
        if (isset($data["password_" . $suffix]) && $data["password_" . $suffix] != null) {

            // se encripta la contraseña con crypt_blowfish
            $crypy = crypt($data["password_" . $suffix], '$2a$07$usesomesillystringforsalt$');

            // se guardan los datos en data
            $data["password_" . $suffix] = $crypy;

            // se solicita la modelo guardar los datos
            $response = PostModel::postData($table, $data);


            // se retorna la respuesta al cliente
            $return = new PostController();
            $return->fncResponse($response, null, $suffix);

        }
    }


    /* ===================================================
        Peticion POST para login usuario
       ===============================================*/

    // se crea una funcion statica 
    // se envia la data y la table
    static public function postLogin($table, $data, $suffix)
    {

        // se pregunta si la contraseña no viena null
        if (isset($data["password_" . $suffix]) && $data["password_" . $suffix] != null) {

            /* ==========================
             validar que el usuario exista en base de datos
             ============================ */

            $response = GetModel::getDataFilter($table, "*", "email_" . $suffix, $data["email_" . $suffix], null, null, null, null);

            if (!empty($response)) {

                /* ==================
                 encriptamos la contraseña
                 ===================== */
                $crypt = crypt($data["password_" . $suffix], '$2a$07$usesomesillystringforsalt$');

                if ($response[0]->{"password_" . $suffix} == $crypt) {

                    /* ==================
                     generar token
                     ===================== */
                    // se pasa le id y el email con el sufijo
                    $token = Connection::jwt($response[0]->{"id_" . $suffix}, $response[0]->{"email_" . $suffix});

                    // [Cambio por IA] Se amplió la clave a 32 caracteres (256 bits) para cumplir con el mínimo requerido por firebase/php-jwt para HS256
                    $jwt = JWT::encode($token, "lksdjflkj3klj4lskdjfklnj23asdfgh", "HS256"); // el HS256 es el algoritmo que se usa para encriptar 

                    /* ================================================
                      Actualizamos la base de datos con el token del usuario
                      =================================================== */
                    // se 
                    $data = array(

                        "token_" . $suffix => $jwt,
                        "token_exp_" . $suffix => $token["exp"]

                    );
                    

                    // solicitamos la actualizacion de los datos del usuario
                    $update = PutModel::putData($table, $data, $response[0]->{"id_" . $suffix}, "id_" . $suffix);

            
                    
                    if (isset($update['comment']) && $update['comment'] == "editado correctamente") {

                        // se devuele los datos actualizado con $response con los datos del token y fecha de expiracion

                        // se pasa el token
                        $response[0]->{"token_" . $suffix} = $jwt;

                        // se pasa la fecha de expiracion
                        $response[0]->{"token_exp_" . $suffix} = $token["exp"];

                        // se devuelve la respuesta
                        $return = new PostController();
                        $return->fncResponse($response, null, $suffix);


                    }

                } else {

                    $response = null;
                    $return = new PostController();
                    $return->fncResponse($response, "clave errada", $suffix);

                }


            } else {

                $response = null;
                $return = new PostController();
                $return->fncResponse($response, "email errado", $suffix);
            }


        }
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
    public function fncResponse($response, $error, $suffix)
    {

        // quitar contraseña de la respuesta
        if(!empty($response[0]-> {"password_".$suffix})){
            
            // con unset se quita la contraseña
            unset($response[0]-> {"password_".$suffix});
        }

        if (!empty($response)) {
            $json = array(
                'status' => 200,
                'total' => count($response),
                'result' => $response
            );
        } else {

            if ($error != null) {
                $json = array(
                    'status' => 404,
                    'result' => $error,
                    'method' => 'post'
                );
            } else {
                $json = array(
                    'status' => 404,
                    'result' => 'no encontrado',
                    'method' => 'post'
                );
            }

        }

        echo json_encode($json, http_response_code($json["status"]));
    }
}
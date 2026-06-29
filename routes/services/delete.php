    <?php

    require_once "models/connection.php";
    require_once "controllers/delete.controller.php";

    // se pregunta si viena la variable id y nameId
    if(isset($_GET['id']) && isset($_GET['nameId'])){

        $columns = array($_GET["nameId"]);


        /* =================================
          Validar tablas y columnas       
          =============================== */

          // si al comprobar la tabla y las columnas esta vacio con getColumnsData de conexion no coincide los nombres
          if(empty(Connection::getColumnsData($table, $columns))){

            // entrega un formato json
            $json = array (

                'status' => 400,
                'result' => 'Error al recibir las columnas'
                
            );

            // se envia la respuesta al cliente
            echo json_encode($json, http_response_code($json["status"]));

            return;

          }
          
           /* =====================================================
           Peticion DELETE para usuario autorizados
           ====================================================== */
          if(isset($_GET["token"])){

          /* =================
             Validar token para realizar la accion
             ================= */
              $tableToken = $_GET['table'] ?? 'users';
            $suffix = $_GET['suffix'] ?? 'user';


            $validate = Connection::tokenValidate($_GET['token'],$tableToken,$suffix);
              
            if($validate === "ok"){


              /* ======================================
            Solicitamos respuesta del controlador para eliminar datos en cualquier tabla
            ====================================== */

            $response = new DeleteController();
            $response->deleteData($table, $_GET['id'], $_GET['nameId']);

            } 
          
           /* =========================
             Token expirado
             ========================= */
            else if($validate === "expired"){
                $json = array(
                    'status' => 303,
                    'results' => "Error: Token expirado"
                );
                echo json_encode($json, http_response_code($json['status']));
                return;
            }
            
            /* =========================
             el token no coincide en la BD
             ========================= */
            else {
                $json = array(
                    'status' => 400,
                    'results' => "Error: Usuario no autorizado"
                );
                echo json_encode($json, http_response_code($json['status']));
                return;
            }

          }
          /* =================
             Error cuando no envia token para realizar la accion
             ================= */
          else{
            $json = array(
              'status' => 400,
              'results' => 'Error: Autorizacion requerida'
            );
            echo json_encode($json, http_response_code($json["status"]));
            return;
          }
    }

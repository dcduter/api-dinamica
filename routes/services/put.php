    <?php

    require_once "models/connection.php";
    require_once "controllers/put.controller.php";

    // se pregunta si viena la variable id y nameId
    if(isset($_GET['id']) && isset($_GET['nameId'])){

    /* ====================================
    Capturar los datos del formulario
    ======================================== */    
        
        $data = array();
        
        // se usa parse_str para que lo pase a un array que se guarda en $data y se elimina los datos vacios con array_filter
        // con file_get_contents se recibe el archivo que viene desde la peticion put 
        parse_str(file_get_contents('php://input'), $data);
        //$data = array_filter($data);

        /* =================================
          Separar la propiedades en un arreglo y el id
        ==================================== */

        $columns = array();

        foreach (array_keys($data) as $key) {
            array_push($columns, $key);
        }

        // validar nameId
        array_push($columns, $_GET['nameId']);
        $columns = array_unique($columns);


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
            Peticion PUT para usuario autorizados
            ====================================================== */
            if(isset($_GET["token"])){


            /* =============================================
              Peticion PUT para usuarios no autorizados 
              ===========================================*/
             if($_GET["token"] == "no" && isset($_GET["except"])){



                  $columns = array($_GET["except"]);

                  /* ====================================================
                      VALIDAR QUE LA TABLA Y SUS COLUMNAS EXISTAN
                  ===============================================*/
                          // ! se usa para invertir el resultado si false == true y si es true == false, si es false seria verdadero y se ejecuta el if
                  if (!Connection::getColumnsData($table, $columns)) {
                          // si algo esta mal se ejecutucuta
                      $json = array(
                        'status' => 404,
                        'result' => 'Escriba de nuevo el nombre de la tabla (POST)'
                          );

                          // json_encode permite devolve el status obtenido en el array en forma de json
                          echo json_encode($json, http_response_code($json['status']));
                          // return para detener la ejecucion del codigo
                          return;
                      }

                // se pide respuesta del controlador para crear datos en cualquier tabla
                  $response = new PutController();
                  $response->putData($data, $table, $_GET['id'], $_GET['nameId']);

             }else{
              /* ========================================
                Peticion PUT para usuario autorizados */

                  // si no viene table y suffix su valor sera user
                  $tableToken = $_GET['table'] ?? 'user';
                  $suffix = $_GET['suffix'] ?? 'user';


                  $validate = Connection::tokenValidate($_GET['token'],$tableToken,$suffix);

                  /* ====================================================================
                  Solicitamos respuesta del controlador para crear datos en cualquier tabla
                  ====================================================================*/

                  if($validate === "ok"){


                    /* ======================================
                  Solicitamos respuesta del controlador para editar datos en cualquier tabla
                  ====================================== */

                  $response = new PutController();
                  $response->putData($data, $table, $_GET['id'], $_GET['nameId']);

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

            /* ===============================
              Error cuando no envia  token para realizar la accion
              ==================================== */
            }else{
              $json = array(
                'status' => 400,
                'results' => "Error: Autorizacion requerida"
              );

              echo json_encode($json, http_response_code($json["status"]));
              return;

            }
    }

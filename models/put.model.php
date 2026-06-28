<?php
// RECIVO LOS DEL CONTROLLADOR

require_once 'connection.php';

require_once 'get.model.php';

class PutModel
{
    /* ===================================================
    Metodo para editar datos de forma dinamica
   ===============================================*/

    static public function putData($table, $data, $id, $nameId)
    {
        /* =================
          Validar el id
        ================== */
        $response = GetModel::getDataFilter($table, $nameId, $nameId, $id, null, null, null, null);

        if (empty($response)) {
            return null;
        }

        /* ===========================================
           Actualizamos los datos de forma dinamica
          =========================================== */
        // se crea dos variable vacias para columnas y parametros

        $set = '';

        foreach ($data as $key => $value) {
            // se arma el query con los campos a actualizar
            $set .= $key . ' = :' . $key . ',';
        }

        // se le quita la ultima coma a $set
        $set = substr($set, 0, -1);

        //  $sql = "UPDATE `courses` SET `id_course`='[value-1]',`title_course`='[value-2]',`description_course`='[value-3]',`id_instructor_course`='[value-4]',`image_course`='[value-5]',`price_course`='[value-6]',`date_created_course`='[value-7]',`date_updated_course`='[value-8]' WHERE 1"

        $sql = "UPDATE $table SET $set WHERE $nameId = :$nameId";
        $link = Connection::connectDataBase();
        $stmt = $link->prepare($sql);
        // enlace de parametros

        foreach ($data as $key => $value) {
            // se enlazan los parametros
            $stmt->bindParam(':' . $key, $data[$key], PDO::PARAM_STR);
        }

        // enlazamos el parametro $nameId para la condicion WHERE
        $stmt->bindParam(':' . $nameId, $id, PDO::PARAM_STR);

        // se ejecuta
        if ($stmt->execute()) {
            $response = array(
                //     "lastId" => $link -> lastInsertId(), // para ver el ultimo id insertado, por eso la conexion se coloco en $link
                'comment' => 'editado correctamente'
            );
            return $response;
        } else {
            return $link->errorInfo();
        }
    }
}

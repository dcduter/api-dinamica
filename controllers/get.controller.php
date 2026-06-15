<?php

require_once 'models/get.model.php';

/**
 * Clase GetController
 *
 * Actúa como el intermediario (Controlador) para procesar solicitudes de lectura (GET).
 * Recibe peticiones desde el servicio de rutas (routes/services/get.php), realiza
 * validaciones primarias y delega la lectura a la base de datos a GetModel.
 *
 * @category Controller
 * @package  Controllers
 */
class GetController
{
    /**
     * Metodo para retornar los datos del modelo.
     *
     * Recibe el nombre de la tabla, invoca al modelo de lectura de base de datos
     * y retorna el listado de registros obtenidos.
     *
     * @param string $table Nombre de la tabla de la base de datos a consultar.
     * @return array|object Devuelve un conjunto de registros como array de objetos obtenidos de la BD.
     */
    /* ========= Peticiones sin filtro ========= */
    static public function getData($table, $select, $orderBy, $orderMode, $startAt, $endAt)
    {
        // Se recibe $select de la vista (get.php)
        // Se envía a get.model.php

        /* Se pide una respuesta del modelo get.model.php */
        $response = GetModel::getData($table, $select, $orderBy, $orderMode, $startAt, $endAt);

        // Se devuelve la respuesta a routes/get.php
        // Se retorna una nueva clase de GetController y se ejecuta el método fncResponse
        $return = new GetController();
        $return->fncResponse($response);  // Se pasa la respuesta del modelo
    }

    /* ========= Peticiones con filtro ========= */
    static public function getDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {
        // Se recibe $select de la vista (get.php)
        // Se envía a get.model.php

        /* Se pide una respuesta del modelo get.model.php */
        $response = GetModel::getDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt);

        // Se devuelve la respuesta al modelo y este a get.php
        // Se retorna una nueva clase de GetController y se ejecuta el método fncResponse
        $return = new GetController();
        $return->fncResponse($response);  // Se pasa la respuesta del modelo
    }

    /* ========= Peticiones GET sin filtro entre tablas relacionadas ========= */

    static public function getRelData($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt)
    {
        /* Se pide una respuesta del modelo get.model.php */
        $response = GetModel::getRelData($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt);

        // Se devuelve la respuesta al modelo y este a get.php
        // Se retorna una nueva clase de GetController y se ejecuta el método fncResponse
        $return = new GetController();
        $return->fncResponse($response);  // Se pasa la respuesta del modelo
    }
    /* ========= Peticiones GET con filtro entre tablas relacionadas ========= */

    static public function getRelDataFilter($rel, $type, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {
        /* Se pide una respuesta del modelo get.model.php */
        $response = GetModel::getRelDataFilter($rel, $type, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt);

        // Se devuelve la respuesta al modelo y este a get.php
        // Se retorna una nueva clase de GetController y se ejecuta el método fncResponse
        $return = new GetController();
        $return->fncResponse($response);  // Se pasa la respuesta del modelo
    }

    /* ============= Peticiones GET para el buscador sin relaciones ============= */
    static public function getDataSearch($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
    {
        // Se recibe $select de la vista (get.php)
        // Se envía a get.model.php

        /* Se pide una respuesta del modelo get.model.php */
        $response = GetModel::getDataSearch($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt);

        // Se devuelve la respuesta al modelo y este a get.php
        // Se retorna una nueva clase de GetController y se ejecuta el método fncResponse
        $return = new GetController();
        $return->fncResponse($response);  // Se pasa la respuesta del modelo
    }

    /* ========= Peticiones GET para el buscador entre tablas relacionadas ========= */

    static public function getRelDataSearch($rel, $type, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
    {
        /* Se pide una respuesta del modelo get.model.php */
        $response = GetModel::getRelDataSearch($rel, $type, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt);

        // Se devuelve la respuesta al modelo y este a get.php
        // Se retorna una nueva clase de GetController y se ejecuta el método fncResponse
        $return = new GetController();
        $return->fncResponse($response);  // Se pasa la respuesta del modelo
    }

    /* ============= Peticiones GET para selecion de rangos ============= */
    static public function getDataRange($table, $select, $linkTo, $between1, $between2, $orderBy, $orderMode, $startAt, $endAt, $filterTo, $inTo) {
        /* Se envia una respuesta del modelo get.model.php */
        $response = GetModel::getDataRange($table, $select, $linkTo, $between1, $between2, $orderBy, $orderMode, $startAt, $endAt, $filterTo, $inTo);

        // Se devuelve la respuesta al modelo y este a get.php
        // Se retorna una nueva clase de GetController y se ejecuta el método fncResponse
        $return = new GetController();
        $return->fncResponse($response);  // Se pasa la respuesta del modelo
    }

    /* ============= Peticiones GET para selecion de rangos con relaciones ============= */
    static public function getRelDataRange($rel, $type, $select, $linkTo, $between1, $between2, $orderBy, $orderMode, $startAt, $endAt, $filterTo, $inTo) {
        /* Se envia una respuesta del modelo get.model.php */
        $response = GetModel::getRelDataRange($rel, $type, $select, $linkTo, $between1, $between2, $orderBy, $orderMode, $startAt, $endAt, $filterTo, $inTo);

        // Se devuelve la respuesta al modelo y este a get.php
        // Se retorna una nueva clase de GetController y se ejecuta el método fncResponse
        $return = new GetController();
        $return->fncResponse($response);  // Se pasa la respuesta del modelo
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
                'result' => 'no encontrado'
            );
        }

        echo json_encode($json, http_response_code($json["status"]));
    }
}

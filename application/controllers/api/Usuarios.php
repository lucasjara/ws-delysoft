<?php
/**
 * Created by PhpStorm.
 * User: Lucas
 * Date: 26-08-2018
 * Time: 19:14
 */

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Usuarios extends REST_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->methods['obtener_usuario']['limit'] = 500; // 500 requests per hour per user/key
    }

    public function obtener_usuario_get()
    {
        $id = $this->get('id');
        $id = (int)$id;
        if ($id <= 0) {
            $this->response(null, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }
        $this->load->model("/administracion/Usuarios_model", "usuarios_model");
        $data = $this->usuarios_model->obtener_usuario($id);
        //$data = $this->usuarios_model->obtener_usuario($id);
        if (!empty($data)) {
            $this->set_response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $arreglo = array('N', 'Usuario No encontrado');
            $this->set_response($arreglo,
                REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function validar_usuario_post()
    {
        $usuario = $this->post('usuario');
        $password = $this->post('password');
        if ($usuario === null && $password === null) {
            $this->response([
                'status' => false,
                'message' => 'Envie todos los Datos Porfavor'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
        $this->load->model("/administracion/Usuarios_model", "usuarios_model");
        $data = $this->usuarios_model->validar_usuario($usuario, $password);
        if ($data != null) {
            $this->set_response(["S", "Credenciales Correctas"],
                REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->set_response(['N', 'Credenciales Incorrectas'],
                REST_Controller::HTTP_OK); // (200) being the HTTP response code
        }
    }

    public function obtener_listado_locales_favoritos_post()
    {
        $id = $this->post('id');
        if ($id <= 0) {
            $this->response("Error no controlado por sistema",
                REST_Controller::HTTP_OK); // BAD_REQUEST (400) being the HTTP response code
        }
        $this->load->model("/administracion/usuarios_model");
        $data = $this->usuarios_model->obtener_locales_favoritos($id);
        $arreglo = array();
        if ($data != null) {
            $arreglo[0] = "S";
            $arreglo[1] = $data;
            $this->set_response($arreglo, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $arreglo[0] = "N";
            $arreglo[1] = 'No cuenta con Locales Favoritos';
            $this->set_response($arreglo,
                REST_Controller::HTTP_OK); // (200) being the HTTP response code
        }
    }

}
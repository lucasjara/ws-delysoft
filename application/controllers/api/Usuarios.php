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

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Usuarios extends REST_Controller {
    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['obtener_usuario']['limit'] = 500; // 500 requests per hour per user/key
        //$this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        //$this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
    }

    public function obtener_usuario($id){
        $this->load->model("/administracion/usuarios_model");
        $data = $this->usuarios_model->obtener_usuario($id);
        if (is_null($data)){
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->response(array('error'=>'Error al conectar a la base de datos'),404);
        }
    }
    public function obtener_usuario_get()
    {
        $id = $this->get('id');

        // Find and return a single record for a particular user.

        $id = (int) $id;

        // Validate the id.
        if ($id <= 0)
        {
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Get the user from the array, using the id as key for retrieval.
        // Usually a model is to be used for this.
        $this->load->model("/administracion/usuarios_model");
        $data = $this->usuarios_model->obtener_usuario($id);

        if (!empty($data))
        {
            $this->set_response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $arreglo = array('N','Usuario No encontrado');
            $this->set_response($arreglo, REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }
}
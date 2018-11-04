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

    public function obtener_usuario($id){
        $this->load->model("/administracion/usuarios_model");
        $data = $this->usuarios_model->obtener_usuario($id);
        if (is_null($data)){
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->response(array('error'=>'Error al conectar a la base de datos'),404);
        }
    }
}
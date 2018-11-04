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
class Usuarios extends REST_Controller
{
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

    public function obtener_usuario_get()
    {
        $id = $this->get('id');
        // Find and return a single record for a particular user.
        $id = (int)$id;
        // Validate the id.
        if ($id <= 0) {
            // Invalid id, set the response and exit.
            $this->response(null, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }
        // Get the user from the array, using the id as key for retrieval.
        // Usually a model is to be used for this.
        $this->load->model("/administracion/usuarios_model");
        $data = $this->usuarios_model->obtener_usuario($id);
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
        // If the id parameter doesn't exist return all the users
        if ($usuario === null && $password === null) {
            $this->response([
                'status' => false,
                'message' => 'Envie todos los Datos Porfavor'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
        $this->load->model("/administracion/usuarios_model");
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

    public function obtener_oferta_productos_post()
    {
        $id = $this->post('id');
        $latitud = $this->post('latitud');
        $longitud = $this->post('longitud');
        if ($id == null || $latitud == null || $longitud == null) {
            $this->response([
                'status' => false,
                'message' => 'Envie todos los Datos Porfavor'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
        $this->load->model("/administracion/usuarios_model");
        // Obtenemos todas las zonas disponibles
        $datos = $this->usuarios_model->obtener_puntos_zona_disponibles();
        if ($datos != null) {
            $constante = "";
            $data_elementos = array();
            $zonas_existe = array();
            // Obtenemos los encabezados de todas las longitudes y latitudes disponibles
            foreach ($datos as $dato) {
                if ($dato->ID != $constante) {
                    array_push($data_elementos, $dato->ID);
                    $constante = $dato->ID;
                }
            }
            $pointLocation = new Geolocalizacion();
            $point = "$longitud $latitud";
            foreach ($data_elementos as $data) {
                $vertices = array();
                for ($i = 0; $i < count($datos); $i++) {
                    if ($data == $datos[$i]->ID) {
                        array_push($vertices, "" . $datos[$i]->LONGITUD . " " . $datos[$i]->LATITUD);
                    }
                }
                $point = str_replace(',', '.', $point);
                $resultado = $pointLocation->pointInPolygon($point, $vertices);
                if ($resultado == "dentro") {
                    array_push($zonas_existe, $data);
                }
            }
            if (!empty($zonas_existe)) {
                $productos = $this->usuarios_model->obtener_productos_zonas($zonas_existe);
                if ($productos != null) {
                    $this->set_response(["S", $productos],
                        REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                } else {
                    $this->set_response(["N", 'No existen productos disponibles de ningun Local'],
                        REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
            } else {
                $this->set_response(['N', 'No se encuentra ningun local Disponible en tu Ubicacion'],
                    REST_Controller::HTTP_OK); // (200) being the HTTP response code
            }
        } else {
            $this->set_response(['N', 'Ninguna zona Disponible'],
                REST_Controller::HTTP_OK); // (200) being the HTTP response code
        }
    }

    public function crear_pedido_local_post()
    {
        $id_prod = $this->post('id_prod');
        $cantidad = $this->post('cantidad');
        $id_usuario = $this->post('id_usuario');
        if ($id_prod == null || $cantidad == null || $id_usuario == null) {
            $this->set_response(['N', 'Envie Todos los Datos Porfavor'], REST_Controller::HTTP_OK);
        }
        // Necesitamos Obtener el Precio y el Local
        $this->load->model("/administracion/usuarios_model");
        $datos = $this->usuarios_model->obtener_info_producto($id_prod);
        if ($datos == null) {
            $this->set_response(['N', 'Error Producto No encontrado'], REST_Controller::HTTP_OK);
        }
        $precio = (int)$datos[0]->PRECIO;
        $id_local = (int)$datos[0]->LOCAL;
        $total = $precio * (int)$cantidad;
        // Creamos el encabezado y el detalle
        $encabezado = $this->usuarios_model->crear_pedido_enc($id_usuario, $id_local, $total);
        if ($encabezado == null) {
            $this->set_response(['N', 'Error al Crear Pedido'], REST_Controller::HTTP_OK);
        }
        $detalle = $this->usuarios_model->crear_pedido_det($encabezado, $id_prod, $cantidad, $precio);
        $this->set_response(["S", "Pedido Realizo con Exito"], REST_Controller::HTTP_OK);
    }

}
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
            $this->set_response(["S", "Credenciales Correctas", $data[0]["ID"]],
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
                        $longitud = $datos[$i]->LONGITUD;
                        $latitud = $datos[$i]->LATITUD;
                        array_push($vertices, "" . $longitud . " " . $latitud);
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
                    $this->set_response(["N", 'Sin productos Disponibles.'],
                        REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
            } else {
                $this->set_response(['N', 'Sin Delyverys Disponibles.'],
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
        $observacion = $this->post('observacion');
        $longitud = $this->post('longitud');
        $latitud = $this->post('latitud');
        if ($id_prod == null || $cantidad == null || $id_usuario == null || $observacion == null) {
            $this->set_response(["N", "Envie Todos los Datos Porfavor"], REST_Controller::HTTP_OK);
        }
        // Necesitamos Obtener el Precio y el Local
        $this->load->model("/administracion/usuarios_model");
        $datos = $this->usuarios_model->obtener_info_producto($id_prod);
        if ($datos == null) {
            $this->set_response(["N", "Error Producto No encontrado"], REST_Controller::HTTP_OK);
        }
        $precio = (int)$datos[0]->PRECIO;
        $id_local = (int)$datos[0]->LOCAL;
        $total = $precio * (int)$cantidad;
        $this->set_response(["N", $latitud], REST_Controller::HTTP_OK);
        // Creamos el encabezado y el detalle
        $encabezado = $this->usuarios_model->crear_pedido_enc($id_usuario, $id_local, $total, $observacion, $longitud,
            $latitud);
        if ($encabezado == null) {
            $this->set_response(["N", "Error al Crear Pedido"], REST_Controller::HTTP_OK);
        }
        $detalle = $this->usuarios_model->crear_pedido_det($encabezado, $id_prod, $cantidad, $precio);
        $obtener_datos_pedido = $this->usuarios_model->obtener_datos_pedido($encabezado);
        $this->set_response(["S", "Pedido Realizo con Exito", $obtener_datos_pedido], REST_Controller::HTTP_OK);
    }

    public function obtener_listado_historico_usuario_post()
    {
        $id_usuario = $this->post('id');
        if ($id_usuario == null) {
            $this->set_response(["N", "Envie Todos los Datos Porfavor"], REST_Controller::HTTP_OK);
        }
        $this->load->model("/administracion/usuarios_model");
        $datos = $this->usuarios_model->obtener_historico_pedidos_usuario($id_usuario);
        if ($datos != null) {
            $this->set_response(["S", $datos], REST_Controller::HTTP_OK);
        } else {
            $this->set_response(["N", "Ningun Pedido Realizado"], REST_Controller::HTTP_OK);
        }
    }

    public function obtener_pedidos_pendientes_repartidor_post()
    {
        $id_repartidor = $this->post('id');
        if ($id_repartidor == null) {
            $this->set_response(["N", "Envie Todos los Datos Porfavor"], REST_Controller::HTTP_OK);
        }
        $this->load->model("/administracion/usuarios_model");
        $datos = $this->usuarios_model->obtener_pedidos_pendientes_repartidor($id_repartidor);
        if ($datos != null) {
            $this->set_response(["S", $datos], REST_Controller::HTTP_OK);
        } else {
            $this->set_response(["N", "Ningun Pedido Pendiente"], REST_Controller::HTTP_OK);
        }
    }

    public function cambiar_estado_pedido_repartidor_post()
    {
        $id_pedido = $this->post('id_pedido');
        $longitud = $this->post('longitud');
        $latitud = $this->post('latitud');
        $estado_pedido = $this->post('estado_pedido');
        if ($id_pedido == null || $estado_pedido == null) {
            $this->set_response(["N", "Envie Todos los Datos Porfavor"], REST_Controller::HTTP_OK);
        }
        $this->load->model("/administracion/usuarios_model");
        switch ($estado_pedido) {
            case "Enviado":
                $this->usuarios_model->cambiar_estado_pedido($id_pedido, 4);
                // Agregar Tracking a Pedido al realizar Cambio
                $id_tracking_enc = $this->usuarios_model->agregar_tracking_pedido();
                $this->usuarios_model->vincular_tracking_pedido($id_pedido, $id_tracking_enc);
                $this->usuarios_model->agregar_tracking_pedido_detalle($id_tracking_enc, $longitud, $latitud, 4);
                $this->set_response(["S", "En Camino"], REST_Controller::HTTP_OK);
                break;
            case "En Camino":
                $this->usuarios_model->cambiar_estado_pedido($id_pedido, 10);
                $id_tracking_enc = $this->usuarios_model->obtener_id_tracking_pedido($id_pedido);
                $this->usuarios_model->agregar_tracking_pedido_detalle($id_tracking_enc, $longitud, $latitud, 10);
                $this->set_response(["S", "En Destino"], REST_Controller::HTTP_OK);
                break;
            case "En Destino":
                $this->usuarios_model->cambiar_estado_pedido($id_pedido, 5);
                $id_tracking_enc = $this->usuarios_model->obtener_id_tracking_pedido($id_pedido);
                $this->usuarios_model->agregar_tracking_pedido_detalle($id_tracking_enc, $longitud, $latitud, 5);
                $this->set_response(["S", "Entregado"], REST_Controller::HTTP_OK);
                break;
            default:
                $this->set_response(["N", "Estado No valido Cambio"], REST_Controller::HTTP_OK);
                break;
        }
    }

    public function actualizar_trancking_repartidor_post()
    {
        $id_pedido = $this->post('id_pedido');
        $longitud = $this->post('longitud');
        $latitud = $this->post('latitud');
        if ($id_pedido == null || $longitud == null || $latitud == null) {
            $this->set_response(["N", "Envie Todos los Datos Porfavor"], REST_Controller::HTTP_OK);
        }
        $this->load->model("/administracion/usuarios_model");
        // Obtener BD
        $estado_pedido = $this->post('estado_pedido');
        switch ($estado_pedido) {
            case "Enviado":
                $this->usuarios_model->cambiar_estado_pedido($id_pedido, 4);
                $this->set_response(["S", "En Camino"], REST_Controller::HTTP_OK);
                break;
            case "En Camino":
                $this->usuarios_model->cambiar_estado_pedido($id_pedido, 10);
                $this->set_response(["S", "En Destino"], REST_Controller::HTTP_OK);
                break;
            case "En Destino":
                $this->usuarios_model->cambiar_estado_pedido($id_pedido, 5);
                $this->set_response(["S", "Entregado"], REST_Controller::HTTP_OK);
                break;
            default:
                $this->set_response(["N", "Estado No valido Cambio"], REST_Controller::HTTP_OK);
                break;
        }
    }
    public function obtener_listado_pedidos_activos_usuario_post()
    {
        $id_usuario = $this->post('id');
        if ($id_usuario == null) {
            $this->set_response(["N", "Envie Todos los Datos Porfavor"], REST_Controller::HTTP_OK);
        }
        $this->load->model("/administracion/usuarios_model");
        $datos = $this->usuarios_model->obtener_listado_pedidos_usuario($id_usuario);
        if ($datos != null) {
            $this->set_response(["S", $datos], REST_Controller::HTTP_OK);
        } else {
            $this->set_response(["N", "Ningun Pedido Realizado"], REST_Controller::HTTP_OK);
        }
    }
}
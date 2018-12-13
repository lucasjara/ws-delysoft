<?php
/**
 * Created by PhpStorm.
 * User: Lucas
 * Date: 19-08-2018
 * Time: 22:08
 */

class Usuarios_model extends CI_Model
{
    public function obtener_usuario($id)
    {
        $this->db->select("*")
            ->from('tb_usuario')
            ->where('ID', $id);
        $query = $this->db->get();
        return $query->result();
    }

    public function validar_usuario($usuario, $password)
    {
        $this->db->select("*")
            ->from('tb_usuario')
            ->where('USUARIO', $usuario)
            ->where('PASSWORD', $password)
            ->where('ACTIVO', 'S');
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result_array() : null;
    }

    public function obtener_locales_favoritos($id)
    {
        $this->db->select("locales.NOMBRE LOCAL,locales.DESCRIPCION")
            ->from('tb_preferencias preferencias')
            ->join("tb_local locales", "locales.ID=preferencias.TB_LOCAL_ID", 'INNER')
            ->where('preferencias.ACTIVO', "S")
            ->where('preferencias.TB_USUARIO_ID', $id);
        $query = $this->db->get();
        return $query->result();
    }

    public function obtener_puntos_zona_disponibles()
    {
        $this->db->select("zona.ID, puntos_zona.LONGITUD, puntos_zona.LATITUD")
            ->from('tb_zona zona')
            ->join("tb_puntos_zona puntos_zona", "zona.ID=puntos_zona.TB_ZONA_ID", 'INNER')
            ->where('puntos_zona.ACTIVO', "S")
            ->order_by("zona.ID");
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result() : null;
    }

    public function obtener_productos_zonas($id_zona)
    {
        $this->db->select("local.NOMBRE LOCAL, producto.ID ID_PRODUCTO, producto.NOMBRE PRODUCTO, producto.DESCRIPCION DESCRIPCION,producto.PRECIO PRECIO")
            ->from('tb_zona_producto prod_zona')
            ->join('tb_zona zona', 'zona.ID=prod_zona.TB_ZONA_ID', 'INNER')
            ->join('tb_producto producto', 'producto.ID=prod_zona.TB_PRODUCTO_ID', 'INNER')
            ->join('tb_local local', 'local.ID=producto.TB_LOCAL_ID', 'INNER')
            ->where('prod_zona.ACTIVO', "S")
            ->where('producto.ACTIVO', "S")
            ->where('zona.ACTIVO', "S");
        $sql_zonas = $id_zona[0];
        for ($i = 1; $i < count($id_zona); $i++) {
            $sql_zonas .= "," . $id_zona[$i];
        }
        $this->db->where("prod_zona.TB_ZONA_ID IN($sql_zonas)");
        $this->db->where("producto.TIPO",2);
        $query = $this->db->get();
        //echo $this->db->last_query();
        return ($query->num_rows() > 0) ? $query->result() : null;
    }

    public function obtener_info_producto($id_producto)
    {
        $this->db->select("producto.PRECIO PRECIO, producto.TB_LOCAL_ID LOCAL")
            ->from('tb_producto producto')
            ->where('producto.ID', $id_producto);
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result() : null;
    }

    public function crear_pedido_enc($id_usuario, $id_local, $total, $observacion, $longitud, $latitud)
    {
        $this->db->set('OBSERVACION', $observacion);
        $this->db->set('TB_USUARIO_SOLICITA_ID', $id_usuario);
        $this->db->set('TB_USUARIO_ENCARGADO_ID', 13); // Repartidor Asignado Directamente por defecto
        $this->db->set('TOTAL', $total);
        $this->db->set('TB_LOCAL_ID', $id_local);
        $this->db->set('TB_ESTADO_PEDIDO_ID', 2);
        $this->db->set('LONGITUD', str_replace(',', '.', $longitud));
        $this->db->set('LATITUD', str_replace(',', '.', $latitud));
        $this->db->set('FECHA', date('Y-m-d'));
        $this->db->set('ACTIVO', 'S');
        $this->db->insert('tb_pedido_enc');
        return $this->db->insert_id();
    }

    public function crear_pedido_det($id_enc, $id_producto, $cantidad, $precio)
    {
        $this->db->set('TB_PEDIDO_ENC_ID', $id_enc);
        $this->db->set('TB_PRODUCTO_ID', $id_producto);
        $this->db->set('CANTIDAD', $cantidad);
        $this->db->set('PRECIO', $precio);
        $this->db->set('ACTIVO', 'S');
        $this->db->insert('tb_pedido_det');
        return $this->db->insert_id();
    }

    public function obtener_datos_pedido($id_encab)
    {
        $this->db->select("
                            encab.ID ID_ENC,
                            producto.NOMBRE PRODUCTO,
                            local.NOMBRE LOCAL,
                            detalle.PRECIO PRECIO,
                            detalle.CANTIDAD CANTIDAD,
                            estado_pedido.NOMBRE ESTADO_PEDIDO,
                            encab.TOTAL TOTAL,
                            encab.OBSERVACION OBSERVACION,
                            'Efectivo' TIPO_PAGO
                            ")
            ->from('tb_pedido_enc encab')
            ->join("tb_pedido_det detalle", "encab.ID=detalle.TB_PEDIDO_ENC_ID", 'INNER')
            ->join("tb_producto producto", "producto.ID=detalle.TB_PRODUCTO_ID", 'INNER')
            ->join("tb_local local", "local.ID=encab.TB_LOCAL_ID", 'INNER')
            ->join("tb_estado_pedido estado_pedido", "estado_pedido.ID=encab.TB_ESTADO_PEDIDO_ID", 'INNER')
            ->where('encab.ID', $id_encab);
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result() : null;
    }

    public function obtener_historico_pedidos_usuario($id_usuario)
    {
        $this->db->select("
                            encab.ID ID_ENC,
                            producto.NOMBRE PRODUCTO,
                            local.NOMBRE LOCAL,
                            detalle.PRECIO PRECIO,
                            detalle.CANTIDAD CANTIDAD,
                            estado_pedido.NOMBRE ESTADO_PEDIDO,
                            encab.TOTAL TOTAL,
                            encab.OBSERVACION OBSERVACION,
                            encab.FECHA FECHA,
                            'Efectivo' TIPO_PAGO
                            ")
            ->from('tb_pedido_enc encab')
            ->join("tb_pedido_det detalle", "encab.ID=detalle.TB_PEDIDO_ENC_ID", 'INNER')
            ->join("tb_producto producto", "producto.ID=detalle.TB_PRODUCTO_ID", 'INNER')
            ->join("tb_local local", "local.ID=encab.TB_LOCAL_ID", 'INNER')
            ->join("tb_estado_pedido estado_pedido", "estado_pedido.ID=encab.TB_ESTADO_PEDIDO_ID", 'INNER')
            ->where('encab.TB_USUARIO_SOLICITA_ID', $id_usuario)
            ->order_by("encab.ID","DESC");
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result() : null;
    }

    public function obtener_pedidos_pendientes_repartidor($id_repartidor)
    {
        $this->db->select("
                            encab.ID ID_ENC,
                            producto.NOMBRE PRODUCTO,
                            local.NOMBRE LOCAL,
                            detalle.PRECIO PRECIO,
                            detalle.CANTIDAD CANTIDAD,
                            estado_pedido.NOMBRE ESTADO_PEDIDO,
                            encab.TOTAL TOTAL,
                            encab.OBSERVACION OBSERVACION,
                            encab.FECHA FECHA,
                            'Efectivo' TIPO_PAGO
                            ")
            ->from('tb_pedido_enc encab')
            ->join("tb_pedido_det detalle", "encab.ID=detalle.TB_PEDIDO_ENC_ID", 'INNER')
            ->join("tb_producto producto", "producto.ID=detalle.TB_PRODUCTO_ID", 'INNER')
            ->join("tb_local local", "local.ID=encab.TB_LOCAL_ID", 'INNER')
            ->join("tb_estado_pedido estado_pedido", "estado_pedido.ID=encab.TB_ESTADO_PEDIDO_ID", 'INNER')
            ->where('encab.TB_USUARIO_ENCARGADO_ID', $id_repartidor);
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result() : null;
    }

    public function cambiar_estado_pedido($id_pedido, $estado_pedido)
    {
        $this->db->set('TB_ESTADO_PEDIDO_ID', $estado_pedido);
        $this->db->where('ID', $id_pedido);
        return $this->db->update('tb_pedido_enc');
    }

    public function agregar_tracking_pedido()
    {
        $this->db->set('FECHA', date('Y-m-d'));
        $this->db->set('ACTIVO', 'S');
        $this->db->insert('tb_tracking_enc');
        return $this->db->insert_id();
    }

    public function vincular_tracking_pedido($id_pedido, $id_tracking_enc)
    {
        $this->db->set('TB_TRACKING_ENC_ID', $id_tracking_enc);
        $this->db->where('ID', $id_pedido);
        return $this->db->update('tb_pedido_enc');
    }

    public function agregar_tracking_pedido_detalle($id_tracking_enc, $longitud, $latitud, $estado_pedido)
    {
        $this->db->set('TB_TRACKING_ENC_ID', $id_tracking_enc);
        $this->db->set('LONGITUD', str_replace(',', '.', $longitud));
        $this->db->set('LATITUD', str_replace(',', '.', $latitud));
        $this->db->set('TB_ESTADO_PEDIDO_ID', $estado_pedido);
        $this->db->set('FECHA', date('Y-m-d'));
        $this->db->set('ACTIVO', 'S');
        $this->db->insert('tb_tracking_det');
        return $this->db->insert_id();
    }
    public function obtener_id_tracking_pedido($id_pedido)
    {
        $this->db->select("
                            encab.TB_TRACKING_ENC_ID ID_ENC,
                            ")
            ->from('tb_pedido_enc encab')
            ->where('encab.ID', $id_pedido);
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result()[0]->ID_ENC : null;
    }
    public function obtener_listado_pedidos_usuario($id_usuario)
    {
        $this->db->select("
                            encab.ID ID_ENC,
                            producto.NOMBRE PRODUCTO,
                            local.NOMBRE LOCAL,
                            detalle.PRECIO PRECIO,
                            detalle.CANTIDAD CANTIDAD,
                            estado_pedido.NOMBRE ESTADO_PEDIDO,
                            encab.TOTAL TOTAL,
                            encab.OBSERVACION OBSERVACION,
                            encab.FECHA FECHA,
                            'Efectivo' TIPO_PAGO,
                            track_det.LONGITUD LONGITUD,
                            track_det.LATITUD LATITUD
                            ")
            ->from('tb_pedido_enc encab')
            ->join("tb_pedido_det detalle", "encab.ID=detalle.TB_PEDIDO_ENC_ID", 'INNER')
            ->join("tb_producto producto", "producto.ID=detalle.TB_PRODUCTO_ID", 'INNER')
            ->join("tb_tracking_enc track_enc", "track_enc.ID=encab.TB_TRACKING_ENC_ID", 'INNER')
            ->join("tb_tracking_det track_det", "track_det.TB_TRACKING_ENC_ID=track_enc.ID", 'INNER')
            ->join("tb_local local", "local.ID=encab.TB_LOCAL_ID", 'INNER')
            ->join("tb_estado_pedido estado_pedido", "estado_pedido.ID=encab.TB_ESTADO_PEDIDO_ID", 'INNER')
            ->where('encab.TB_USUARIO_SOLICITA_ID', $id_usuario)
            ->where('encab.FECHA', date('Y-m-d'))
            ->where('encab.TB_ESTADO_PEDIDO_ID IN (2,4,5,10)')
            ->where("track_det.ID=(
                                    SELECT tracking_det.ID FROM tb_tracking_det tracking_det 
                                    WHERE tracking_det.ACTIVO='S' 
                                    AND tracking_det.TB_TRACKING_ENC_ID=track_det.TB_TRACKING_ENC_ID
                                    ORDER BY tracking_det.ID desc LIMIT 1 
                                )")
            ->order_by("encab.ID","ASC");
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result() : null;
    }
    public function obtener_local_usuario_administrativo($id_usuario)
    {
        $this->db->select("
                            local_usuario.TB_LOCAL_ID ID_LOCAL
                            ")
            ->from('tb_local_usuario local_usuario')
            ->where('local_usuario.TB_USUARIO_ID', $id_usuario)
            ->where('local_usuario.TB_PERFIL_ID','4');
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result()[0]->ID_LOCAL : null;
    }
    public function obtener_informacion_local($id_local)
    {
        $this->db->select("
                            encab.ID ID_ENC,
                            producto.NOMBRE PRODUCTO,
                            local.NOMBRE LOCAL,
                            detalle.PRECIO PRECIO,
                            detalle.CANTIDAD CANTIDAD,
                            estado_pedido.NOMBRE ESTADO_PEDIDO,
                            encab.TOTAL TOTAL,
                            encab.OBSERVACION OBSERVACION,
                            encab.FECHA FECHA,
                            'Efectivo' TIPO_PAGO,
                            track_det.LONGITUD LONGITUD,
                            track_det.LATITUD LATITUD
                            ")
            ->from('tb_pedido_enc encab')
            ->join("tb_local local", "local.ID=encab.TB_LOCAL_ID", 'INNER')
            ->join("tb_estado_pedido estado_pedido", "estado_pedido.ID=encab.TB_ESTADO_PEDIDO_ID", 'INNER')
            ->where('encab.TB_USUARIO_SOLICITA_ID', $id_usuario)
            ->where('encab.FECHA', date('Y-m-d'))
            ->where('encab.TB_ESTADO_PEDIDO_ID IN (2,4,5,10)')
            ->where("track_det.ID=(
                                    SELECT tracking_det.ID FROM tb_tracking_det tracking_det 
                                    WHERE tracking_det.ACTIVO='S' 
                                    AND tracking_det.TB_TRACKING_ENC_ID=track_det.TB_TRACKING_ENC_ID
                                    ORDER BY tracking_det.ID desc LIMIT 1 
                                )")
            ->order_by("encab.ID","ASC");
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result() : null;
    }
}
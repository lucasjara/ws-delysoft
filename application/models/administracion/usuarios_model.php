<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

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
        $this->db->select('*')
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
        return ($query->num_rows() > 0) ? $query->result() : null;
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
}
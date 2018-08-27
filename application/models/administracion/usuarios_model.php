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
        ->where('ID',$id);
        $query = $this->db->get();
        return $query->result();
    }
}
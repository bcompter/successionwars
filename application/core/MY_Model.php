<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model 
{
    var $table = '';
    var $table_id = '';
    var $select = null;
    var $user_id = '';

    function __construct()
    {
        parent::__construct();
    }

    function create($obj)
    {
        $this->db->insert($this->table, $obj);
    }

    function get_by_id($id)
    {
        $this->db->where($this->table_id, $id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }

    function delete($id)
    {
        $this->db->where($this->table_id, $id);
        $this->db->delete($this->table);
    }

    function update($id, $obj)
    {	
        $this->db->where($this->table_id, $id);
        $this->db->update($this->table, $obj);
    }

    function set_select($s)
    {
        $this->db->select($s);
    }

    function set_join($j, $on)
    {
        $this->db->join($j, $on);
    }

}
<?php

/*
 * Territories represent the game locations that units may occupy.
 * Territories have a resource value and may hold manufacturing centers.
 */

Class Pathmodel extends MY_Model {
    
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'path_id';
        $this->table = 'paths';
    }
    
    function map_jumpexists($RID1,$RID2)
    {
        //mysql call to search for jump ID1 to ID2

        $this->db->where('origin_id', $RID1);
        $this->db->where('destination_id', $RID2);
        $this->db->limit(1);
        $map_jumpexistsresult = $this->db->get($this->table)->result();

        if (count($map_jumpexistsresult) > 0)
            return true;
        else
            return false;
    }
    
    function get_opposing_path_by_id($PID1)
    {
        //mysql call to search for an opposing jump by a jump ID

        $this->db->where('path_id', $PID1);
        $this->db->limit(1);
        $map_jump = $this->db->get($this->table)->row();

        $this->db->where('origin_id', $map_jump->destination_id);
        $this->db->where('destination_id', $map_jump->origin_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
}

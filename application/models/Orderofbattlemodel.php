<?php

/*
 * orderofbattle
 */

Class Orderofbattlemodel extends MY_Model {
        
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'orderofbattle_id';
        $this->table = 'orderofbattle';
    }
    
    /**
     * Get all games created by the user id given
     * @param type $owner_id
     * @return type 
     */
    function get_by_creator($owner_id)
    {
        $this->db->where('creator_id', $owner_id);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all games
     */
    function get_all()
    {
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all data components for an order of battle
     * @param type $id
     * @return type 
     */
    function get_data($id)
    {
        $this->db->where('oob_id', $id);
        return $this->db->get('orderofbattledata')->result();
    }
    
    /**
     * Get all the player data types
     * @param type $id 
     */
    function get_players($id)
    {
        $this->db->where('oob_id', $id);
        $this->db->where('type', 0);
        return $this->db->get('orderofbattledata')->result();
    }
    
    /**
     * Get all the territory data types
     * @param type $id 
     */
    function get_territories($id)
    {
        $this->db->where('oob_id', $id);
        $this->db->where('type', 2);
        return $this->db->get('orderofbattledata')->result();
    }
    
    function get_regions($oob_id)
    {
        return $this->db->query('select map.name, map.map_id from map JOIN orderofbattledata on orderofbattledata.arg1data=map.map_id where oob_id='.$oob_id.' and type=2 ORDER BY map.name ASC')->result();
    }
    
    function get_regions_by_world($world_id)
    {
        return $this->db->query('select map.name, map.map_id from map where world_id='.$world_id.' ORDER BY map.name ASC')->result();
    }
    
    function get_region($mapid)
    {
        $this->db->where('map_id', $mapid);
        $this->db->limit(1);
        return $this->db->get('map')->row();
    }
    
    /**
     * Create a new order of battle data point
     * @param type $data 
     */
    
    function create_data($data)
    {
        $query = 'insert into orderofbattledata (oob_id, type,arg0column,arg0data'.
        (isset($data->arg1column) ? ',arg1column,arg1data' : '').
        (isset($data->arg2column) ? ',arg2column,arg2data' : '').
        (isset($data->arg3column) ? ',arg3column,arg3data' : '').
        (isset($data->arg4column) ? ',arg4column,arg4data' : '').
        (isset($data->arg5column) ? ',arg5column,arg5data' : '').
        (isset($data->arg6column) ? ',arg6column,arg6data' : '').
        (isset($data->arg7column) ? ',arg7column,arg7data' : '').
        (isset($data->arg8column) ? ',arg8column,arg8data' : '');
        $query .= ') ';
        
        $query .= 'values ('.$data->oob_id.','.$data->type.',"'.$data->arg0column.'","'.$data->arg0data.'"'.
        (isset($data->arg1column) ? ',"'.$data->arg1column.'","'.$data->arg1data.'"' : '').
        (isset($data->arg2column) ? ',"'.$data->arg2column.'","'.$data->arg2data.'"' : '').
        (isset($data->arg3column) ? ',"'.$data->arg3column.'","'.$data->arg3data.'"' : '').
        (isset($data->arg4column) ? ',"'.$data->arg4column.'","'.$data->arg4data.'"' : '').
        (isset($data->arg5column) ? ',"'.$data->arg5column.'","'.$data->arg5data.'"' : '').
        (isset($data->arg6column) ? ',"'.$data->arg6column.'","'.$data->arg6data.'"' : '');
        
        if (isset($data->arg7column))
        {
            $query .= ',"'.$data->arg7column.'"';
            if (isset($data->arg7data))
                $query .= ',"'.$data->arg7data.'"';
            else
                $query .= ',NULL';   
        }
        
        if (isset($data->arg8column))
        {
            $query .= ',"'.$data->arg8column.'"';
            if (isset($data->arg8data))
                $query .= ',"'.$data->arg8data.'"';
            else
                $query .= ',NULL';   
        }
        
        $query .= ') ';
        
        $this->db->query($query);
    }
    
    function edit_data($data)
    {
//example:
//UPDATE  `successionwars`.`orderofbattledata` SET  `arg4data` =  'Blue' WHERE  `orderofbattledata`.`data_id` =22;            
        $query = "UPDATE `orderofbattledata` SET ".
        (isset($data->arg0data) ? "`arg0data` = '".$data->arg0data."'" : '').
        (isset($data->arg1data) ? ", `arg1data` = '".$data->arg1data."'" : '').
        (isset($data->arg2data) ? ", `arg2data` = '".$data->arg2data."'" : '').
        (isset($data->arg3data) ? ", `arg3data` = '".$data->arg3data."'" : '').
        (isset($data->arg4data) ? ", `arg4data` = '".$data->arg4data."'" : '').
        (isset($data->arg5data) ? ", `arg5data` = '".$data->arg5data."'" : '').
        (isset($data->arg6data) ? ", `arg6data` = '".$data->arg6data."'" : '');
        
        if (isset($data->arg7data))
        {
            $query .= ", `arg7data` = '".$data->arg7data."'";
        }
        else
        {
            $query .= ", `arg7data` = NULL";
        }
        
        if (isset($data->arg8data))
        {
            $query .= ", `arg8data` = '".$data->arg8data."'";
        }
        else
        {
            $query .= ", `arg8data` = NULL";
        }
        
        $query .= " WHERE  `orderofbattledata`.`data_id` =".$data->data_id.";";
        
        $this->db->query($query);
    }
        
}

?>

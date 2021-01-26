<?php

Class Worldmodel extends MY_Model {

    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'world_id';
        $this->table = 'worlds';
    }
  
    /**
     * Get a particular world by id, include the designer info
     */
    function get_by_id($world_id)
    {
        return $this->db->query('SELECT worlds.*, users.id, users.username FROM worlds JOIN users ON users.id=worlds.user_id WHERE world_id='.$world_id)->row();
    }
    
    /**
     * Get all worlds
     */
    function get_all()
    {
        return $this->db->query('SELECT worlds.*, users.id, users.username FROM worlds JOIN users ON users.id=worlds.user_id WHERE 1')->result();        
    }
    
}  // end worldmodel

?>
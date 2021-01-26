<?php

/*
 * 
 * 
 */

Class Combatlogmodel extends MY_Model {

    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'combatlog_id';
        $this->table = 'combatlog';
    }
    
    function get_by_player($player_id)
    {
        $this->db->select('map.name, combatlog.*');
        $this->db->join('territories', 'territories.territory_id=combatlog.territory_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('combatlog.player_id', $player_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_game($game_id)
    {
        $this->db->where('game_id', $game_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_player_territory($player_id, $territory_id)
    {
        $this->db->where('player_id', $player_id);
        $this->db->where('territory_id', $territory_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_by_territory($territory_id)
    {
        $this->db->where('territory_id', $territory_id);
        return $this->db->get($this->table)->result();
    }
    
}

?>

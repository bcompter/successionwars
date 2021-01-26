<?php

/*
 * Jumpships
 */

Class Jumpshipmodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'jumpship_id';
        $this->table = 'jumpships';
    }
    
    /**
     * Get all jumpships in the game
     * Used during game builds
     * @param type $game_id 
     */
    function get_by_game($game_id)
    {
        $this->db->join('players', 'players.player_id=jumpships.owner_id');
        $this->db->where('game_id', $game_id);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all of the player owned jumpships
     * 
     * @param type $player_id
     * @return type 
     */
    function get_by_player($player_id)
    {
        $this->db->select('jumpships.*, map.*, territories.*, jumpships.name as jumpship_name, map.name as territory_name');
        $this->db->where('owner_id', $player_id);
        $this->db->join('territories','territories.territory_id=jumpships.location_id');
        $this->db->join('map','territories.map_id=map.map_id');
        $this->db->order_by('map.name','asc');
        $this->db->order_by('jumpships.capacity','desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_not_placed($player_id)
    {
        $this->db->where('owner_id', $player_id);
        $this->db->where('location_id', null);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all of the jumpships in a particular area
     * 
     * @param type $player_id
     * @return type 
     */
    function get_by_territory($territory_id)
    {
        $this->db->where('location_id', $territory_id);
        $this->db->join('players','players.player_id=jumpships.owner_id', 'left');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Overrides the default to add in the join statement
     * @param type $jumpship_id 
     */
    function get_by_jumpshipid($jumpship_id)
    {
        $this->db->select('jumpships.*, map.name, players.faction, jumpships.name as jumpship_name');
        $this->db->where('jumpship_id', $jumpship_id);
        $this->db->join('territories','territories.territory_id=jumpships.location_id');
        $this->db->join('map','map.map_id=territories.map_id');
        $this->db->join('players', 'players.player_id=jumpships.owner_id');
        $this->db->limit(1);
        return $this->db->get($this->table)->row(); 
    }
    

    /**
     * Used in combat to capture leaders
     * 
     * @param type $territory_id
     * @return type 
     */
    function get_captured_by_territory($territory_id)
    {
        $query = 'SELECT * FROM jumpships '
                . 'JOIN territories on territories.territory_id=jumpships.location_id '
                . 'WHERE location_id='.$territory_id.' '
                . 'AND jumpships.owner_id != territories.player_id';

        return $this->db->query($query)->result();
    }
    
    /**
     * Used in production and factory views
     * 
     * @param type $player_id
     * @return type 
     */
    function get_under_construction($player_id)
    {
        $this->db->where('owner_id', $player_id);
        $this->db->where('being_built', true);
        return $this->db->get($this->table)->result();
    }
    
    function deletejumpship($jumpship_id)
    {
        $this->db->where('jumpship_id',$jumpship_id);
        return $this->db->delete($this->table);
    }
    
    function get_not_in_own_territory($game_id)
    {
        if ($this->debug>2) log_message('error', '.../models/jumpshipmodel.php get_not_in_own_territory($game_id='.$game_id.')');
        return $this->db->query('SELECT jumpships.*, territories.*, players.faction, map.name AS territory_name, territories.player_id AS new_owner_player_id
FROM jumpships
JOIN territories ON territories.territory_id = jumpships.location_id
JOIN players ON players.player_id=jumpships.owner_id
JOIN map ON map.map_id = territories.map_id
WHERE territories.game_id ='.$game_id.'
AND jumpships.owner_id <> territories.player_id')->result();
    }
    
    /**
     * Drop all units and leaders loading into this jumpship
     */
    function drop_all($jumpship_id)
    {
        $this->db->query('update combatunits set loaded_in_id=null where loaded_in_id='.$jumpship_id);
        $this->db->query('update leaders set loaded_in_id=null where loaded_in_id='.$jumpship_id);
    }
    
}
?>

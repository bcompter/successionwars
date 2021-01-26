<?php

/*
 * Leaders
 */

Class Leadermodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'leader_id';
        $this->table = 'leaders';
    }
    
    function get_by_id($leader_id)
    {
        $this->db->select('leaders.*, map.name AS territory_name, players.faction');
        $this->db->join('territories', 'territories.territory_id=leaders.location_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->join('players', 'players.player_id=leaders.controlling_house_id');
        $this->db->where('leaders.leader_id', $leader_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();    
    }
    
    function get_by_id_to_place($leader_id)
    {
        $this->db->where('leaders.leader_id', $leader_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();    
    }
    
    function get_by_player($player_id)
    {
        $this->db->where('controlling_house_id', $player_id);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all original leaders of a player
     */
    function get_original_by_player($player_id)
    {
        $this->db->where('original_house_id', $player_id);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all house leaders as well as any merc leaders controlled by a player
     */
    function get_all_by_player($player_id)
    {
        $query = 'select leaders.*, map.name as location from leaders
            JOIN territories ON territories.territory_id = leaders.location_id
            JOIN map ON map.map_id = territories.map_id
            where original_house_id='.$player_id.'
            or allegiance_to_house_id='.$player_id;
        return $this->db->query($query)->result();
    }

    function get_by_territory($territory_id)
    {
        $this->db->select('leaders.*, territories.*, players.faction');
        $this->db->join('territories', 'territories.territory_id=leaders.location_id');
        $this->db->join('players', 'players.player_id=leaders.controlling_house_id','left');
        $this->db->where('leaders.location_id', $territory_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_not_in_own_territory($game_id)
    {
        return $this->db->query('
            SELECT leaders.*, territories.*, players.faction, map.name AS territory_name, territories.player_id AS new_controlling_player_id
            FROM leaders
            JOIN territories ON territories.territory_id = leaders.location_id
            JOIN players ON players.player_id=leaders.controlling_house_id
            JOIN map ON map.map_id = territories.map_id
            WHERE leaders.game_id ='.$game_id.'
            AND leaders.controlling_house_id <> territories.player_id')->result();
    }
    
    function get_by_player_territory($player_id, $territory_id)
    {
        $this->db->select('leaders.*, territories.*, players.faction');
        $this->db->join('territories', 'territories.territory_id=leaders.location_id');
        $this->db->join('players', 'players.player_id=leaders.controlling_house_id');
        $this->db->where('leaders.controlling_house_id', $player_id);
        $this->db->where('leaders.location_id', $territory_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_jumpship($jumpship_id)
    {
        $this->db->select('leaders.*, players.faction');
        $this->db->where('loaded_in_id', $jumpship_id);
        $this->db->join('players', 'players.player_id=leaders.controlling_house_id');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Used after movement is complete to reset the ability to be loaded
     * 
     * @param type $game_id 
     */
    function get_by_was_loaded($game_id)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('was_loaded <>', 0);
        return $this->db->get($this->table)->result();
    }
    
    function get_captured_by_territory($territory_id)
    {
        return $this->db->query('SELECT * FROM leaders '
                . 'JOIN territories on territories.territory_id=leaders.location_id '
                . 'WHERE location_id='.$territory_id.' '
                . 'AND leaders.controlling_house_id != territories.player_id')->result();
    }
    
    function get_by_game($game_id)
    {
        $this->db->where('game_id', $game_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_not_placed($player_id)
    {
        $this->db->where('controlling_house_id', $player_id);
        $this->db->where('location_id', null);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_player_taxes($player_id)
    {   
        $this->db->join('territories', 'territories.territory_id=leaders.location_id');
        $this->db->where('player_id', $player_id);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get the leader associated with a mercenary unit
     */
    function get_by_merc($game_id, $name)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('associated_units', $name);
        return $this->db->get($this->table)->result();
    }
    
    function get_admin_tax($player_id)
    {   
        if ($this->debug > 2)
            log_message('error', '.../models/leadermodel.php/get_admin_tax($player_id='.$player_id.')');
        
        $this->db->join('territories', 'territories.territory_id=leaders.location_id');
        $this->db->where('player_id', $player_id);
        $this->db->where('original_house_id', $player_id);
        $this->db->select_sum('admin');
        $add1 = $this->db->get($this->table)->row()->admin;
        
        $this->db->join('territories', 'territories.territory_id=leaders.location_id');
        $this->db->where('player_id', $player_id);
        $this->db->where('original_house_id !=', $player_id);
        $this->db->where('allegiance_to_house_id', $player_id);
        $this->db->where('controlling_house_id', $player_id);
        $this->db->select_sum('admin');
        $add2 = $this->db->get($this->table)->row()->admin;
        
        $total = 0;
        $total = (isset($add1)?$add1:0) + (isset($add2)?$add2:0);
        
        return $total;
    }    
    
    function get_negative_admin_tax($player_id)
    {   
        if ($this->debug > 2)
            log_message('error', '.../models/leadermodel.php/get_admin_tax($player_id='.$player_id.')');
        
        $this->db->join('territories', 'territories.territory_id=leaders.location_id');
        $this->db->where('player_id', $player_id);
        $this->db->where('original_house_id', $player_id);
        $this->db->where('admin <', 0);
        $this->db->select_sum('admin');
        $add1 = $this->db->get($this->table)->row()->admin;
        
        $this->db->join('territories', 'territories.territory_id=leaders.location_id');
        $this->db->where('player_id', $player_id);
        $this->db->where('original_house_id !=', $player_id);
        $this->db->where('allegiance_to_house_id', $player_id);
        $this->db->where('controlling_house_id', $player_id);
        $this->db->where('admin <', 0);
        $this->db->select_sum('admin');
        $add2 = $this->db->get($this->table)->row()->admin;
        
        $total = 0;
        $total = (isset($add1)?$add1:0) + (isset($add2)?$add2:0);
        
        return $total;
    }   
    
    /**
     * Get all leaders captured by a player
     * Used for victory conditions
     */
    function get_by_player_pow($player_id)
    {
        return $this->db->query('SELECT leaders.* FROM leaders WHERE '
                . 'controlling_house_id='.$player_id.' '
                . 'AND original_house_id<>controlling_house_id')->result();
    }
}


?>

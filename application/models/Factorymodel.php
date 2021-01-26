<?php

Class Factorymodel extends MY_Model {
    
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'factory_id';
        $this->table = 'factories';
    }
    
    /**
     * Get all factories in the game
     * Used during game builds
     * @param type $game_id 
     */
    function get_by_game($game_id)
    {
        $this->db->join('territories', 'territories.territory_id=factories.location_id');
        $this->db->where('game_id', $game_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_id($factory_id)
    {
        $this->db->join('territories', 'territories.territory_id=factories.location_id');
        $this->db->join('map','territories.map_id=map.map_id');
        $this->db->where('factory_id', $factory_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_by_location($location_id)
    {
        $this->db->where('location_id', $location_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_by_player($player_id)
    {
        $this->db->join('territories', 'territories.territory_id=factories.location_id');
        $this->db->join('map','territories.map_id=map.map_id');
        $this->db->where('player_id', $player_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_under_construction($player_id)
    {
        $this->db->join('territories', 'territories.territory_id=factories.location_id');
        $this->db->where('player_id', $player_id);
        $this->db->where('being_built', 1);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all factories requiring a damage roll based on combat logs
     * 
     */
    function get_by_damage_roll($game_id)
    {
        $this->db->select('factories.*, combatlog.force_size, map.name');
        $this->db->join('territories', 'territories.territory_id=factories.location_id');
        $this->db->join('combatlog', 'combatlog.territory_id=factories.location_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('territories.game_id', $game_id);
        $this->db->where('force_size IS NOT NULL', null, false);
        
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all factories requiring a damage roll based on combat logs for a 
     * given location.
     */
    function get_by_damage_location($location_id)
    {
        $this->db->select('factories.*, combatlog.force_size, combatlog.use_force_size, map.name');
        $this->db->join('territories', 'territories.territory_id=factories.location_id');
        $this->db->join('combatlog', 'combatlog.territory_id=factories.location_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('territories.territory_id', $location_id);
        $this->db->where('force_size IS NOT NULL', null, false);
        $this->db->limit(1);
        
        return $this->db->get($this->table)->row();
    }
    
    function get_repair($game_id)
    {
        $this->db->join('territories', 'territories.territory_id=factories.location_id');
        $this->db->where('being_repaired', true);
        $this->db->where('game_id', $game_id);
        return $this->db->get($this->table)->result();
    }
}
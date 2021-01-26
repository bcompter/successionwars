<?php

/*
 * Map
 */

Class Mapmodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'map_id';
        $this->table = 'map';
    }
    
    /**
     * Generate a database result with all data required to load the map for display
     */
    function get_by_game($game_id)
    {
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        $this->db->select('map.*, territories.game_id,territories.territory_id, territories.resource, territories.player_id, territories.is_periphery, players.faction, players.color, players.text_color, COUNT(DISTINCT leader_id) as num_leaders, COUNT(DISTINCT jumpship_id) as num_jumpships, COUNT(DISTINCT combatunit_id) as num_units, territories.is_capital, territories.is_regional, factories.factory_id, factories.is_damaged');
        $this->db->join('territories', 'territories.map_id=map.map_id');
        $this->db->join('players', 'players.player_id=territories.player_id', 'left');
        $this->db->join('leaders', 'leaders.location_id=territories.territory_id', 'left');
        $this->db->join('combatunits', 'combatunits.location_id=territories.territory_id', 'left');
        $this->db->join('jumpships', 'jumpships.location_id=territories.territory_id', 'left');
        $this->db->join('factories', 'factories.location_id=territories.territory_id', 'left');
        $this->db->where('territories.game_id', $game_id);        
        $this->db->group_by('territory_id');
        $this->db->order_by('territory_id');
        return $this->db->get($this->table)->result();
    }
    
    function get_by_game_time($game_id, $timestamp)
    {
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        $this->db->select('map.*, territories.game_id,territories.territory_id, territories.resource, territories.player_id,territories.last_update, players.faction, players.color, players.text_color, COUNT(DISTINCT leader_id) as num_leaders, COUNT(DISTINCT jumpship_id) as num_jumpships, COUNT(DISTINCT combatunit_id) as num_units, territories.is_capital, territories.is_regional, factories.factory_id, factories.is_damaged');
        $this->db->join('territories', 'territories.map_id=map.map_id');
        $this->db->join('players', 'players.player_id=territories.player_id');
        $this->db->join('leaders', 'leaders.location_id=territories.territory_id', 'left');
        $this->db->join('combatunits', 'combatunits.location_id=territories.territory_id', 'left');
        $this->db->join('jumpships', 'jumpships.location_id=territories.territory_id', 'left');
        $this->db->join('factories', 'factories.location_id=territories.territory_id', 'left');
        $this->db->where('territories.game_id', $game_id);
        $this->db->where('last_update >', $timestamp);
        $this->db->group_by('territory_id');
        $this->db->order_by('territory_id');
        return $this->db->get($this->table)->result();
    }
    
    function get_sum_strength($game_id)
    {
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        $this->db->select('sum(strength) as sum_strength');
        $this->db->join('territories', 'territories.map_id=map.map_id');
        $this->db->join('combatunits', 'combatunits.location_id=territories.territory_id', 'left');
        $this->db->where('territories.game_id', $game_id);
        $this->db->group_by('territory_id');
        $this->db->order_by('territory_id');
        return $this->db->get($this->table)->result();
    }
    
    function get_sum_capacity($game_id)
    {
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        $this->db->select('sum(capacity) as sum_capacity');
        $this->db->join('territories', 'territories.map_id=map.map_id');
        $this->db->join('jumpships', 'jumpships.location_id=territories.territory_id', 'left');
        $this->db->where('territories.game_id', $game_id);
        $this->db->group_by('territory_id');
        $this->db->order_by('territory_id');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get a map but only for one player if blind deployment is enabled
     */
    function get_by_game_blind($game_id, $player_id)
    {
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        $this->db->select('map.*, territories.game_id,territories.territory_id, territories.resource, territories.player_id, territories.is_periphery, players.faction, players.color, players.text_color, COUNT(DISTINCT leader_id) as num_leaders, COUNT(DISTINCT jumpship_id) as num_jumpships, COUNT(DISTINCT combatunit_id) as num_units');
        $this->db->join('territories', 'territories.map_id=map.map_id');
        $this->db->join('players', 'players.player_id=territories.player_id', 'left');
        $this->db->join('leaders', 'leaders.location_id=territories.territory_id', 'left');
        $this->db->join('combatunits', 'combatunits.location_id=territories.territory_id', 'left');
        $this->db->join('jumpships', 'jumpships.location_id=territories.territory_id', 'left');
        $this->db->where('territories.game_id', $game_id);        
        $this->db->group_by('territory_id');
        $this->db->order_by('territory_id');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get a map but only for one player if blind deployment is enabled
     */
    function get_by_game_time_blind($game_id, $timestamp, $player_id)
    {
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        $this->db->select('map.*, territories.game_id,territories.territory_id, territories.resource, territories.player_id,territories.last_update, players.faction, players.color, players.text_color, COUNT(DISTINCT leader_id) as num_leaders, COUNT(DISTINCT jumpship_id) as num_jumpships, COUNT(DISTINCT combatunit_id) as num_units');
        $this->db->join('territories', 'territories.map_id=map.map_id');
        $this->db->join('players', 'players.player_id=territories.player_id');
        $this->db->join('leaders', 'leaders.location_id=territories.territory_id', 'left');
        $this->db->join('combatunits', 'combatunits.location_id=territories.territory_id', 'left');
        $this->db->join('jumpships', 'jumpships.location_id=territories.territory_id', 'left');
        $this->db->where('territories.game_id', $game_id);
        $this->db->where('last_update >', $timestamp);
        $this->db->group_by('territory_id');
        $this->db->order_by('territory_id');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get a map but only for one player if blind deployment is enabled
     */
    function get_sum_strength_blind($game_id, $player_id)
    {
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        $this->db->select('sum(strength) as sum_strength');
        $this->db->join('territories', 'territories.map_id=map.map_id');
        $this->db->join('combatunits', 'combatunits.location_id=territories.territory_id', 'left');
        $this->db->where('territories.game_id', $game_id);
        $this->db->group_by('territory_id');
        $this->db->order_by('territory_id');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get a map but only for one player if blind deployment is enabled
     */
    function get_sum_capacity_blind($game_id, $player_id)
    {
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        $this->db->select('sum(capacity) as sum_capacity');
        $this->db->join('territories', 'territories.map_id=map.map_id');
        $this->db->join('jumpships', 'jumpships.location_id=territories.territory_id', 'left');
        $this->db->where('territories.game_id', $game_id);
        $this->db->group_by('territory_id');
        $this->db->order_by('territory_id');
        return $this->db->get($this->table)->result();
    }
    
    function get_all()
    {
        $this->db->order_by('world_id', 'asc');
        $this->db->order_by('name');
        return $this->db->get($this->table)->result();
    }
    
    function get_all_by_world($world_id)
    {
        $this->db->where('world_id', $world_id);
        $this->db->order_by('map_id', 'asc');
        return $this->db->get($this->table)->result();
    }
    
    // USED ONLY IN PATH TOOL !!!
    function get_adjacent_paths($map_id)
    {
        $this->db->where('origin_id', $map_id);
        $this->db->join('paths','map.map_id=paths.destination_id');
        return $this->db->get($this->table)->result();
    }
       
}

?>
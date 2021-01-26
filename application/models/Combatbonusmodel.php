<?php

/* Track combat bonueses from various sources.
 * Bonuses can affect locations or individual combat units.
 * 
 * Source_Type
 * 0 = Card
 * 1 = Leader
 * 2 = Technology
 * 
 */

Class Combatbonusmodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'combatbonus_id';
        $this->table = 'combatbonus';
    }
    
    function get_by_player($player_id)
    {
        $this->db->where('player_id', $player_id);
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
        $this->db->where('location_id', $territory_id);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all combat bonuses that affect a combatunit
     */
    function get_by_unit($unit_id)
    {
        $this->db->where('combatunit_id', $unit_id);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all technology combat bonuses that affect a combatunit
     * Because you can't have more than one technology combat bonus
     */
    function get_tech_by_unit($unit_id)
    {
        $this->db->where('source_type', 2);
        $this->db->where('combatunit_id', $unit_id);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Return all combat bonuses in a territory
     * 
     * @param type $territory_id
     * @return type 
     */
    function get_by_territory($territory_id)
    {
        $this->db->join('players', 'players.player_id=combatbonus.player_id');
        $this->db->where('location_id', $territory_id);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get the sum of all combat bonuses for a given combat unit
     * 
     * @param type $combatunit_id The ID of the combat unit
     */
    function get_unit_bonus( $combatunit_id )
    {
        $this->db->select_sum('value');
        $this->db->where('combatunit_id', $combatunit_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    /**
     * Get all the combat bonuses from contested areas
     * This is used to create a quick lookup table when performing combat
     * 
     * @param type $game_id The ID of the game
     */
    function get_by_contested($game_id)
    {
        $this->db->select('combatbonus.*');
        $this->db->join('territories', 'territories.territory_id=combatbonus.location_id');
        $this->db->where('combatbonus.game_id', $game_id);
        $this->db->where('is_contested', true);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all bonuses from a given leader
     */
    function get_by_leader($leader_id)
    {
        if ($this->debug>2) log_message('error', 'models/combatbonusmodel.php/get_by_leader($leader_id='.$leader_id.')');
        $this->db->select('combatbonus.*, combatunits.*, map.name AS territory_name');
        $this->db->join('combatunits', 'combatunits.combatunit_id=combatbonus.combatunit_id');
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('source_id', $leader_id);
        $this->db->where('source_type', 1);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    /**
     * Delete all combat bonuses from a player
     * 
     * Used when a player gains 23 or 24 technology points to eliminate existing 
     * combat bonuses.  Fixes tracker 116.
     */
    function delete_by_player($player_id)
    {
        $this->db->query('delete from combatbonus where source_id='.$player_id);
        $this->db->query('update players set tech_bonus=0 where player_id='.$player_id);
    }
    
    /**
     * Decrement all combat bonuses in the game
     */
    function decrement($game_id)
    {
        $this->db->query('update combatbonus set ttl = (ttl - 1) where game_id='.$game_id);
    }
    
    /**
     * Delete all combat bonuses with a ttl less than 1
     */
    function delete_invalid($game_id)
    {
        $this->db->query('delete from combatbonus where ttl < 1 and game_id='.$game_id);
    }
}

?>

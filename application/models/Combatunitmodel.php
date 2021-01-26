<?php

/*
 * Combat units
 * 
 * A combat unit with a strength of 0 is not in play.  It may have been killed
 * or just not deployed at the start of the game.
 * 
 */

Class Combatunitmodel extends MY_Model {
    
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'combatunit_id';
        $this->table = 'combatunits';
    }
    
    /**
     * Get all live combatunits in a territory that belong to a particular player
     * 
     * This is used in combat when a kill is assigned to check if a combatlog 
     * should be zero'd out.
     * 
     * @param type $location_id
     * @param type $player_id
     * @return type 
     */
    function get_by_location_player($location_id, $player_id)
    {
        $this->db->where('location_id', $location_id);
        $this->db->join('players','players.player_id=combatunits.owner_id');
        $this->db->where('die', false);
        $this->db->where('strength >', 0);
        $this->db->where('player_id', $player_id);
        $this->db->order_by('combatunits.is_conventional','asc');
        $this->db->order_by('combatunits.is_elemental','asc');
        $this->db->order_by('combatunits.is_merc','asc');
        $this->db->order_by('combatunits.name','asc');
        $this->db->order_by('combatunits.strength','desc');
        $this->db->order_by('combatunits.prewar_strength','desc');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get a list of unique players by territory
     * Used to determine if a territory is truely contested to avoid generating 
     * combat logs where not required
     */
    function get_by_territory($territory_id)
    {
        return $this->db->query('SELECT * FROM combatunits WHERE location_id='.$territory_id.' GROUP BY owner_id')->result();
    }
    
    function get_by_unit_id($id)
    {
        $this->db->select('combatunits.*, map.name as territory');
        $this->db->where('combatunit_id', $id);
        $this->db->join('territories','territories.territory_id=combatunits.location_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_by_location($location_id)
    {
        $this->db->join('players','players.player_id=combatunits.owner_id', 'left');
        $this->db->where('location_id', $location_id);
        $this->db->where('strength >', 0);
        $this->db->order_by('combatunits.owner_id','asc');
        $this->db->order_by('combatunits.is_conventional','asc');
        $this->db->order_by('combatunits.is_elemental','asc');
        $this->db->order_by('combatunits.is_merc','asc');   
        $this->db->order_by('combatunits.name','asc');
        $this->db->order_by('combatunits.strength','desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_houses_by_location($location_id)
    {
        $this->db->select('players.*, map.name as territory');
        $this->db->join('players','players.player_id=combatunits.owner_id', 'left');
        $this->db->where('location_id', $location_id);
        $this->db->where('strength >', 0);
        $this->db->group_by('player_id');
        return $this->db->get($this->table)->result();
    }  
    
    /**
     * Fetch a Comstar unit for killing automatically
     * @param type $game_id 
     */
    function get_comstar($game_id)
    {
        $this->db->join('territories','territories.territory_id=combatunits.location_id');
        $this->db->where('combatunits.game_id', $game_id);
        $this->db->where('owner_id', 0);
        $this->db->where('die !=', 1);
        $this->db->order_by('combatunits.strength', 'asc');
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    /**
     * Fetch a Neutral unit by location for killing automatically
     * @param type $game_id 
     */
    function get_comstar_by_territory($game_id, $territory_id)
    {
        $this->db->join('territories','territories.territory_id=combatunits.location_id');
        $this->db->where('combatunits.game_id', $game_id);
        $this->db->where('owner_id', 0);
        $this->db->where('die !=', 1);
        $this->db->where('location_id', $territory_id);
        $this->db->order_by('combatunits.strength', 'asc');
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    /**
     * Fetch a single unit to be killed automatically
     * 
     * Used to kill neutral units
     * And also the nuclear strike card 
     */
    function get_by_player_territory($player_id, $territory_id)
    {
        return $this->db->query('select * from combatunits where
            die=0 and
            owner_id='.$player_id.' and
            location_id='.$territory_id.' and
            strength > 0
            order by strength asc limit 1           
            ')->row();
    }
    
    function get_one_by_location($location_id)
    {
        $this->db->where('location_id', $location_id);
        $this->db->where('strength >', 0);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_by_game($game_id)
    {
        $this->db->select('combatunits.*, map.name AS territory_name, players.faction');
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->join('players', 'players.player_id=combatunits.owner_id');
        $this->db->where('players.game_id', $game_id);
        $this->db->where('strength >', 0);
        $this->db->order_by('combatunits.owner_id','asc');
        $this->db->order_by('combatunits.is_conventional','asc');
        $this->db->order_by('combatunits.is_elemental','asc');
        $this->db->order_by('combatunits.is_merc','asc');
        $this->db->order_by('combatunits.name','asc');
        $this->db->order_by('combatunits.strength','desc');
        $this->db->order_by('combatunits.prewar_strength','desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_by_game_contested($game_id)
    {
        $this->db->select('combatunits.*, map.name AS territory_name, players.faction');
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->join('players', 'players.player_id=combatunits.owner_id');
        $this->db->where('territories.game_id', $game_id);
        $this->db->where('strength >', 0);
        $this->db->where('is_contested', 1);
        $this->db->order_by('combatunits.owner_id','asc');
        $this->db->order_by('combatunits.is_conventional','asc');
        $this->db->order_by('combatunits.is_elemental','asc');
        $this->db->order_by('combatunits.is_merc','asc');
        $this->db->order_by('combatunits.name','asc');
        $this->db->order_by('combatunits.strength','desc');
        $this->db->order_by('combatunits.prewar_strength','desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_all_by_game($game_id)
    {
        $this->db->select('combatunits.*, players.faction');
        $this->db->join('players', 'players.player_id=combatunits.owner_id');
        $this->db->where('players.game_id', $game_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_game_combined($game_id)
    {
        $this->db->join('players', 'players.player_id=combatunits.owner_id');        
        $this->db->where('combatunits.game_id', $game_id);
        $this->db->where('combine_with >', 0);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_player($player_id)
    {
        $this->db->select('combatunits.*, map.name AS territory_name');
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('owner_id', $player_id);
        $this->db->where('strength >', 0);
        $this->db->order_by('combatunits.is_conventional','asc');
        $this->db->order_by('combatunits.is_elemental','asc');
        $this->db->order_by('combatunits.is_merc','asc');
        $this->db->order_by('combatunits.name','asc');
        $this->db->order_by('combatunits.strength','desc');
        $this->db->order_by('combatunits.prewar_strength','desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_by_player_all($player_id)
    {
        $this->db->select('combatunits.*, map.name AS territory_name');
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id', 'left');
        $this->db->join('map', 'map.map_id=territories.map_id', 'left');
        $this->db->where('owner_id', $player_id);
        $this->db->where('is_conventional', '=0');
        $this->db->where('is_elemental', '=0');
        $this->db->order_by('combatunits.name','asc');

        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get total military strength of a player
     * Used for victory conditions
     */
    function get_by_player_strength($player_id)
    {
        return $this->db->query('SELECT SUM(strength) as strength FROM combatunits WHERE '
                . 'owner_id='.$player_id.' '
                . 'AND strength > 0')->row()->strength;
    }
    
    function get_by_tech_bonus($player_id)
    {
        $this->db->select('combatunits.*, map.name AS territory_name');
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('owner_id', $player_id);
        $this->db->where('strength >', 0);
        $this->db->where('is_conventional', false);
        $this->db->where('is_elemental', false);
        $this->db->order_by('combatunits.is_merc','asc');
        $this->db->order_by('combatunits.name','asc');
        $this->db->order_by('combatunits.strength','desc');
        $this->db->order_by('combatunits.prewar_strength','desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_all_by_player($player_id)
    {
        $this->db->select('combatunits.*, territories.name AS territory_name');
        $this->db->where('owner_id', $player_id);
        $this->db->where('strength >', 0);
        $this->db->order_by('combatunits.is_conventional','asc');
        $this->db->order_by('combatunits.is_elemental','asc');
        $this->db->order_by('combatunits.is_merc','asc');
        $this->db->order_by('combatunits.name','asc');
        $this->db->order_by('combatunits.strength','desc');
        $this->db->order_by('combatunits.prewar_strength','desc');        
        return $this->db->get($this->table)->result();
    }
    
    function get_not_placed($player_id)
    {
        $this->db->select('combatunits.*');
        $this->db->where('owner_id', $player_id);
        $this->db->where('strength >', 0);
        $this->db->where('location_id', null);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_jumpship($jumpship_id)
    {
        $this->db->where('loaded_in_id', $jumpship_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_loaded($game_id)
    {
        $this->db->join('territories','territories.territory_id=jumpships.location_id');
        $this->db->where('combatunits.game_id', $game_id);
        $this->db->where('loaded_in_id <>', null);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_die( $game_id )
    {
        $this->db->where('combatunits.game_id', $game_id);
        $this->db->where('die', true);
        $this->db->join('territories','territories.territory_id=combatunits.location_id');
        return $this->db->get($this->table)->result();
    }
    
    function get_by_killed( $player_id )
    {
        $this->db->where('owner_id', $player_id);
        $this->db->where('strength', 0);
        $this->db->where('being_built', 0);
        $this->db->order_by('combatunits.is_merc','asc');
        $this->db->order_by('combatunits.name','asc');
        $this->db->order_by('combatunits.strength','desc');
        $this->db->order_by('combatunits.prewar_strength','desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_by_rebuild( $player_id )
    {
        $this->db->where('owner_id', $player_id);
        $this->db->where('strength', 0);
        $this->db->where('being_built', 0);
        $this->db->where('is_rebuild', 1);
        $this->db->order_by('combatunits.is_merc','asc');
        $this->db->order_by('combatunits.name','asc');
        $this->db->order_by('combatunits.strength','desc');
        $this->db->order_by('combatunits.prewar_strength','desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_under_construction( $player_id )
    {
        $this->db->where('owner_id', $player_id);
        $this->db->where('being_built', true);
        return $this->db->get($this->table)->result();
    }
    
    function get_under_construction_by_game( $game_id )
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('being_built', true);
        return $this->db->get($this->table)->result();
    }    
    
    function num_unassigned( $player_id )
    {
        $this->db->where('target_id', null);
        $this->db->where('combatunits.owner_id', $player_id);
        $this->db->where('strength >', 0);
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id');
        $this->db->where('is_contested', true);
        
        return $this->db->count_all_results( $this->table );
    }
    
    /*
     * This function fetches all combatunits with a lastroll other than 0
     * Used after combat is done to clear out results for the next round.
     */
    function get_by_roll($game_id)
    {
        $this->db->where('combatunits.game_id', $game_id);
        $this->db->where('last_roll >', 0);
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Used after movement is complete to reset the ability to be loaded
     * 
     * @param type $game_id 
     */
    function get_by_was_loaded($game_id)
    {
        $this->db->join('players', 'players.player_id=combatunits.owner_id');
        $this->db->where('combatunits.game_id', $game_id);
        $this->db->where('was_loaded <>', 0);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Return the size of the force attacking an location
     * Used to determine the roll modifier for factory damage rolls
     * 
     * @param type $player_id
     * @param type $territory_id 
     */
    function get_force_size($player_id, $territory_id)
    {
        $this->db->select_sum('strength');
        $this->db->where('owner_id', $player_id);
        $this->db->where('location_id', $territory_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_upgradable($location_id)
    {
        $this->db->where('location_id', $location_id);
        $this->db->where('strength >', 0);
        $this->db->where('strength <', 4);
        $this->db->where('is_conventional', false);
        $this->db->where('is_elemental', false);
        return $this->db->get($this->table)->result();
    }
    
    function mercs($game_id)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('strength >', 0);
        $this->db->where('is_merc', true);
        $this->db->where('owner_id', null);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Fetch a random unaffiliated mercenary unit from the game for the mercenary phase
     */
    function get_random_merc($game_id)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('strength', 0);
        $this->db->where('is_merc', true);
        $this->db->where('owner_id', null);
        $this->db->order_by('combatunit_id','random');
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    /**
     * Fetch all mercenaries that need to be placed
     * 
     * Called during the mercenary phase to place newly minted merc forces
     */
    function mercs_to_place($game_id)
    {
        return $this->db->query('select * from combatunits
            where game_id='.$game_id.
            ' and strength > 0 and
            is_merc=true and
            owner_id is not null
            and location_id is null')->result();
    }
    
    /**
     * Get all Mercenary units in the game with the same name 
     * Used to select mercs for the mercenary phase
     */
    function get_by_name($game_id, $name)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('name', $name);
        $this->db->where('strength', 0);
        $this->db->where('is_merc', true);
        $this->db->where('owner_id', null);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Used in contract ends
     */
    function get_by_game_name($game_id, $name)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('name', $name);
        $this->db->where('is_merc', true);
        $this->db->order_by('combatunit_id', 'asc');
        return $this->db->get($this->table)->result();
    }
    
    function all_mercs($game_id)
    {
        $this->db->select('combatunits.*, territories.*, players.faction, map.name as territory_name');
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id');
        $this->db->join('players', 'players.player_id=combatunits.owner_id');
        $this->db->join('map', 'territories.map_id=map.map_id');
        $this->db->where('territories.game_id', $game_id);
        $this->db->where('strength >', 0);
        $this->db->where('is_merc', true);
        $this->db->order_by('combatunits.owner_id','asc');
        $this->db->order_by('combatunits.name','asc');
        $this->db->order_by('combatunits.strength','desc');
        $this->db->order_by('combatunits.prewar_strength','desc');
        return $this->db->get($this->table)->result();
    }
    
    function mercs_for_hire($game_id)
    {
        $this->db->select('combatunits.*, territories.*, map.name as territory_name');
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id', 'left');
        $this->db->join('map', 'territories.map_id=map.map_id', 'left');
        $this->db->where('owner_id', null);
        $this->db->where('combatunits.game_id', $game_id);
        $this->db->where('strength >', 0);
        $this->db->where('is_merc', true);
        $this->db->order_by('combatunit_id', 'asc');
        return $this->db->get($this->table)->result();
    }
    
    function get_by_leader($game_id, $name)
    {
        if ($this->debug>2) log_message('error', 'models/combatunitmodel.php/get_by_leader($game_id='.$game_id.', $name='.$name.')');
        $this->db->select('combatunits.*, map.name AS territory_name, players.faction');
        $this->db->join('players', 'players.player_id=combatunits.owner_id');
        $this->db->join('territories', 'territories.territory_id=combatunits.location_id','left');
        $this->db->join('map', 'map.map_id=territories.map_id','left');
        $this->db->where('players.game_id', $game_id);
        $this->db->where('combatunits.name', $name);
        $this->db->order_by('combatunits.strength','desc');
        return $this->db->get($this->table)->result();
    }
       
    function get_by_leader_combined( $leader_id )
    {
        $this->db->where('combined_by', $leader_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_name_and_owner( $garrison_name, $owner_id )
    {
        $this->db->where('name', $garrison_name);
        $this->db->where('owner_id', $owner_id);
        return $this->db->get($this->table)->result();
    }
}

?>
<?php

/*
 * Territories represent the game locations that units may occupy.
 * Territories have a resource value and may hold manufacturing centers.
 */

Class Territorymodel extends MY_Model {
    
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'territory_id';
        $this->table = 'territories';
    }
    
    function get_by_id($territory_id)
    {
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('territory_id', $territory_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_by_game($game_id)
    {
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('game_id', $game_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_game_name($game_id, $name)
    {
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('game_id', $game_id);
        $this->db->where('name', $name);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_by_game_map($game_id, $map_id)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('map_id', $map_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_by_game_periphery($game_id)
    {
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('game_id', $game_id);
        $this->db->where('is_periphery', true);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_game_capitals($game_id)
    {
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('game_id', $game_id);
        $this->db->where('is_capital', true);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all capitals owned by a player.
     * Used to determine victory conditions
     */
    function get_by_player_capitals($player_id)
    {
        $this->db->where('player_id', $player_id);
        $this->db->where('is_capital', true);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all regional capitals owned by a player.
     * Used to determine victory conditions
     */
    function get_by_player_regional($player_id)
    {
        $this->db->where('player_id', $player_id);
        $this->db->where('is_regional', true);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get the current house capital for a player
     */
    function get_capital($player_id)
    {
        // Default to original capital if owned by the player in question
        $player = $this->db->query('select * from players where player_id='.$player_id)->row();
        $capital = $this->db->query('select * from territories
            join map on map.map_id=territories.map_id
            where territory_id='.$player->original_capital)->row();
        
        if (isset($player->player_id) && isset($capital->player_id))
        {
            if ($player->player_id == $capital->player_id)
                return $capital;
        }
        
        // Otherwise, select a valid capital
        return $this->db->query('select * from territories  
            join map on map.map_id=territories.map_id
            where player_id='.$player_id.' 
            order by is_capital desc, is_regional desc limit 1')->row();
    }
    
    /**
     * Modified for placement hopefully does not break
     */
    function get_by_player($player_id)
    {
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('player_id', $player_id);
        $this->db->order_by('name', 'asc');
        return $this->db->get($this->table)->result();
    }
    
    function get_adjacent($territory_id, $game_id)
    {
        $t = $this->get_by_id($territory_id);
        
        $this->db->select('name, territory_id, destination_id, is_periphery, is_contested, player_id');
        $this->db->where('origin_id', $t->map_id);
        $this->db->where('game_id', $game_id);
        $this->db->join('paths','territories.map_id=paths.destination_id');
        $this->db->join('map', 'map.map_id=territories.map_id');
        return $this->db->get($this->table)->result();
    }
    
    // USED ONLY IN PATH TOOL !!!
    function get_adjacent_paths($map_id)
    {
        $this->db->where('origin_id', $map_id);
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->join('paths','territories.map_id=paths.destination_id');
        return $this->db->get($this->table)->result();
    }
    
    function is_contested($territory_id)
    {
        $this->db->where('location_id', $territory_id);
        $this->db->where('strength >', 0);  // Make sure we are only checking live units
        $this->db->select('owner_id');
        $retval = $this->db->get('combatunits')->result();

        $this->db->where('territory_id', $territory_id);
        $this->db->select('player_id');
        $territory = $this->db->get($this->table)->row();
        
        foreach($retval as $unit)
        {
            if ( $unit->owner_id != $territory->player_id )
                return true;
        }
        return false;
    }
    
    function get_contested($game_id)
    {
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('game_id', $game_id);
        $this->db->where('is_contested', true);
        return $this->db->get($this->table)->result();
    }
    
    function player_owns_terra($player)
    {
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('game_id', $player->game_id);
        $this->db->where('player_id', $player->player_id);
        $this->db->where('map.name', 'Terra');
        $terracheck = $this->db->get($this->table)->row();
        
        if (count($terracheck) > 0)
            return true;
        else
            return false;
    }
    
    function get_contested_involved($game_id, $player_id)
    {
//log_message('error', '$game_id: '.$game_id);
//log_message('error', '$player_id: '.$player_id);
        $this->db->join('map', 'map.map_id=territories.map_id');
        $this->db->where('game_id', $game_id);
        $this->db->where('is_contested', 1);
        $result_contested = $this->db->get($this->table)->result();
//        if ($result_contested === FALSE)
//            log_message('error', 'query is FALSE');
//        if ($result_contested !== FALSE)
//            log_message('error', 'query is true');

        //$contested_regions = mysql_fetch_array($result_contested);
        $contested_regions = $result_contested;
//        log_message('error', 'starting foreach()');
        foreach($contested_regions as $key => $contested_region)
        {
//            log_message('error', 'foreach on '.$contested_region->territory_id.': '.$key);
//            log_message('error', 'count($contested_region): '.count($contested_region));
//            foreach($contested_region as $key => $dataitem)
//            {
//                log_message('error', '$contested_region '.$key.': '.$dataitem);
//            }
            // get units in the region
            $this->db->where('location_id', $contested_region->territory_id);
            $this->db->where('strength >', 0);  // Make sure we are only checking live units
            $this->db->where('owner_id', $player_id);  // Make sure we are only checking live units
            //$this->db->select('owner_id');
            $retval = $this->db->get('combatunits')->result();
//            log_message('error', 'tried second query');
//            log_message('error', 'count($retval): '.count($retval));            
            if ($retval === FALSE)
                log_message('error', 'error in game '.$game_id.' second combats query failed in territorymodel.php');
            if (count($retval) > 0)
            {
                $this->load->model('combatlogmodel');
                $combatlog = $this->combatlogmodel->get_by_player_territory($player_id, $contested_region->territory_id);  
                              
                if (is_countable($combatlog) && count($combatlog) > 0  && $combatlog->casualties_owed > 0)
                    $contested_region->involved = 2;
                else 
                    $contested_region->involved = 1;
            }
            else
                $contested_region->involved = 0;
        }
//        echo 'break</ br>';
//echo print_r ($contested_regions);        
//        log_message('error', 'right before: return $contested_regions;');
        return $contested_regions;
    }
    
    // TO be more efficient, pass in an actual territory
    function is_captured($territory)
    {   
        $factions_found = $this->db->query('select combatunit_id from combatunits 
                where location_id = '.$territory->territory_id.'
                group by owner_id
                ')->result();
        $num_factions = count($factions_found);
        if ($num_factions <= 1)
            return true;
        else
            return false;
    }
}

?>
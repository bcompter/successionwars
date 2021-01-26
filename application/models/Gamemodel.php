<?php

/*
 * Game
 */

Class Gamemodel extends MY_Model {
        
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'game_id';
        $this->table = 'games';
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
    
    function allUnitsPlaced($game_id)
    {
        // Check for combatunits
        $this->db->where('game_id', $game_id);
        $this->db->where('location_id', null);
        $this->db->where('strength !=', 0);
        $c = $this->db->count_all_results('combatunits');
        
        // Check for jumpships
        $this->db->join('players','players.player_id=jumpships.owner_id');
        $this->db->where('game_id', $game_id);
        $this->db->where('location_id', null);
        $j = $this->db->count_all_results('jumpships');
        
        // Check for leaders
        $l = $this->db->query('select count(*) as number from leaders where
            game_id='.$game_id.' and 
            location_id is null and
            controlling_house_id is not null')->row()->number;

        if ($c == 0 && $j == 0 && $l==0)
            return true;
        else
            return false;
    }
    
    /**
     * Get all games the user is playing in
     * @param type $user_id 
     */
    function get_by_user($user_id)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->join('players', 'games.game_id = players.game_id');
        $this->db->where('players.user_id', $user_id);
        
        return $this->db->get()->result();
    }
    
    /**
     * Get the five most recently created games
     * @return type 
     */
    function get_recent()
    {
        $this->db->limit(5);
        $this->db->order_by('game_id','desc');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all games
     */
    function get_all()
    {
        $this->db->from($this->table);
        $this->db->select('games.*, players.faction as faction');   
        $this->db->join('players', 'players.player_id=games.player_id_playing', 'left');
        return $this->db->get()->result();
    }
    
    /**
     * Get all games created by the user id given
     * @param type $owner_id
     * @return type 
     */
    function get_open()
    {
        $this->db->select('*, count(players.player_id) as num_open_slots');
        $this->db->from($this->table);
        $this->db->join('players', 'games.game_id = players.game_id');
        $this->db->where('players.user_id', null);
        $this->db->group_by('games.game_id');
        return $this->db->get()->result();
    }
    
    function is_game_open ($game_id=0)
    {
        $this->db->from($this->table);
        $this->db->join('players', 'games.game_id = players.game_id');
        $this->db->where('games.game_id', $game_id);
        $this->db->where('players.user_id', null);
        $this->db->limit(1);
        
        $result = $this->db->get()->row();
        if (isset($result->game_id))
            return TRUE;
        else
            return FALSE;
    }
    
}

?>

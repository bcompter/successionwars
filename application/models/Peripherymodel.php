<?php

/*
 * Handle periphery bids
 */

Class Peripherymodel extends MY_Model {
        
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'bid_id';
        $this->table = 'peripherybids';
    }
    
    /**
     * Get all bids for the given territory id
     * 
     * @param type $id
     * @return type 
     */
    function get_by_territory($id)
    {
        $this->db->where('nation_id', $id);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all open bids for a particular game.
     * 
     * This detects when a hold should be in place
     * @param type $id 
     */
    function get_by_game($id)
    {
        $this->db->join('players', 'peripherybids.player_id = players.player_id');
        $this->db->where('game_id', $id);
        return $this->db->get($this->table)->result();
    }
    
    /***
     * Get all bids by a certain player
     */
    function get_by_player_id($player_id)
    {
        $this->db->where('player_id', $player_id);
        $this->db->limit(1);        
        return $this->db->get($this->table)->row();
    }     
}
?>

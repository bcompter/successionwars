<?php

/**
 * 
 */
Class Offermodel extends MY_Model 
{
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'offer_id';
        $this->table = 'mercoffers';
    }
    
    function get_by_merc($merc_id)
    {
        $this->db->where('merc_id', $merc_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_merc_player($merc_id, $player_id)
    {
        $this->db->where('merc_id', $merc_id);
        $this->db->where('player_id', $player_id);
        return $this->db->get($this->table)->result();
    }
    
    function get_by_player_id($player_id)
    {
        $this->db->where('player_id', $player_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row_array();
    }
    
    function get_by_game_id($game_id)
    {
        $this->db->join('players','players.player_id=mercoffers.player_id');
        $this->db->where('game_id', $game_id);
        return $this->db->get($this->table)->result();
    }
}

?>

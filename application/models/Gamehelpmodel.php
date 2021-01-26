<?php

/**
 * Game help
 */

Class Gamehelpmodel extends MY_Model {
        
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'help_id';
        $this->table = 'game_help';
    }
  
    /**
     * Get the game help row associated with a game
     */
    function get_by_game($game_id)
    {
        $this->db->where('game_id', $game_id);
        return $this->db->get($this->table)->row();
    }
    
    /**
     * Get all help requests with an active 
     */
    function get_active()
    {
        $this->db->select('*, game_help.description as help_description');
        $this->db->join('games', 'games.game_id=game_help.game_id');
        $this->db->where('status !=', 0);
        return $this->db->get($this->table)->result();
    }
    
}  // end Class Gamehelpmodel

?>

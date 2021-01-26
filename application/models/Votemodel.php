<?php

/**
 * 
 */
Class Votemodel extends MY_Model 
{
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'vote_id';
        $this->table = 'game_owner_votes';
    }
    
    /**
     * Get all votes from a particular game
     */
    function get_by_game($game_id)
    {
        return $this->db->query('SELECT game_owner_votes.*, players.*, users.username, target_users.username as vote_username
            FROM game_owner_votes
            JOIN players on players.player_id=game_owner_votes.player_id
            JOIN users on players.user_id=users.id
            JOIN players as target_players on target_players.player_id=game_owner_votes.target_id
            JOIN users as target_users on target_users.id=target_players.user_id
            WHERE game_owner_votes.game_id='.$game_id
            )->result();
        
    }  // end get_by_game
    
    /**
     * Get a current players votes in a game
     */
    function get_by_player_game($player_id, $game_id)
    {
        $this->db->join('players', 'players.player_id='.$this->table.'.player_id');
        $this->db->where($this->table.'.player_id', $player_id);
        $this->db->where($this->table.'.game_id', $game_id);
        return $this->db->get($this->table)->result();
        
    }  // end get_by_player_game
    
}  // end Votemodel

?>

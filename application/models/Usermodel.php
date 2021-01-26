<?php

/*
 * Players in a game
 */

Class Usermodel extends MY_Model {
        
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'id';
        $this->table = 'users';
    }

    function posts_per_page($user_id)
    {
        $this->db->where('users.id', $user_id);
        $this->db->limit(1);
        //return $this->db->get($this->table.forum_posts_per_page);
        return $this->db->get($this->table)->row();
    }
    
    /**
     * Get all players in a given game
     * @param type $game_id
     * @return type 
     */
    function get_by_game($game_id)
    {
        $this->db->where('game_id', $game_id);
        $this->db->join('users','players.user_id=users.id', 'left');
        $this->db->order_by('turn_order', 'asc');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all empty player slots in a given game
     * @param type $game_id
     * @return type 
     */
    function get_by_game_open($game_id)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('user_id', null);
        $this->db->join('users','players.user_id=users.id', 'left');
        return $this->db->get($this->table)->result();
    }

    function get_by_game_and_user_id($game_id,$user_id)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('players.user_id', $user_id);
        $this->db->join('users','players.user_id=users.id', 'left');
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }    
    
    /**
     * Join a game by setting the user id to the new player
     * @param type $player 
     */
    function join_game($player)
    {
        $this->db->where('player_id', $player['player_id']);
        $this->db->update($this->table, $player);
    }
    
    function someone_using_elementals($game_id)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('may_build_elementals IS NOT NULL', NULL, FALSE);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function email_on_private_message($player_id)
    {
        $this->db->where('player_id', $player_id);
        $this->db->where('users.email_on_private_message', 1);
        $this->db->join('users','players.user_id=users.id', 'left');
        $this->db->limit(1);
        if ($this->db->get($this->table)->row())
            return TRUE;
        else
            return FALSE;
    }
}
?>

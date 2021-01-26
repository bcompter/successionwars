<?php

/*
 * This model deals with cards.  The cards database table will track all cards
 * in the deck, discard pile, or in players hands.
 */

Class Chatmodel extends MY_Model {
    
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'chat_id';
        $this->table = 'chat';
    }
    
    /**
     * Get the last # messages in a given game
     * @param type $game_id 
     */
    function get_last_ten($game_id)
    {        
        $this->db->select('chat.*, players.faction, players.player_id, players.color, to_players.faction as to_faction');
        $this->db->join('players','players.player_id = chat.player_id');
        $this->db->join('players as to_players', 'to_players.player_id=chat.to_player_id', 'left');
        $this->db->where('players.game_id',$game_id);
        $this->db->limit(20);
        $this->db->order_by('time_stamp', 'desc');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get the last # messages in a given game
     * @param type $game_id 
     */
    function get_last($game_id, $offset)
    {        
        $this->db->select('chat.*, players.faction, players.player_id, players.color, to_players.faction as to_faction');
        $this->db->join('players','players.player_id = chat.player_id');
        $this->db->join('players as to_players', 'to_players.player_id=chat.to_player_id', 'left');
        $this->db->where('players.game_id',$game_id);
        $this->db->limit(20, $offset);
        $this->db->order_by('time_stamp', 'desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_num_chats($game_id)
    {
        $this->db->join('players','players.player_id = chat.player_id');
        $this->db->join('players as to_players', 'to_players.player_id=chat.to_player_id', 'left');
        $this->db->where('players.game_id',$game_id);
        return $this->db->count_all_results($this->table);
    }
    
    /**
     * Get any new messages that have been sent...
     * 
     * @param type $game_id
     * @param type $time
     * @return type 
     */
    function get_new($game_id, $time)
    {
        $this->db->select('chat.*, players.faction, players.player_id, players.color, to_players.faction as to_faction');
        $this->db->join('players','players.player_id = chat.player_id');
        $this->db->join('players as to_players', 'to_players.player_id=chat.to_player_id', 'left');
        $this->db->where('players.game_id',$game_id);
        $this->db->where('time_stamp >', $time);
        $this->db->where('players.game_id', $game_id);
        $this->db->where('chat.game_id', $game_id);
        $this->db->order_by('time_stamp', 'desc');
        return $this->db->get($this->table)->result();
    }
}

?>

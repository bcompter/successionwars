<?php

/*
 * This model deals with cards.  The cards database table will track all cards
 * in the deck, discard pile, or in players hands.
 */

Class Chatglobalmodel extends MY_Model {
    
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'chat_id';
        $this->table = 'public_chat';
    }
    
    /**
     * Get the last # messages 
     */
    function get_last()
    {        
        $this->db->select('public_chat.*, users.username');
        $this->db->join('users', 'users.id=public_chat.user_id');
        $this->db->limit(20, 0);
        $this->db->order_by('time_stamp', 'desc');
        return $this->db->get($this->table)->result();
    }

    /**
     * Get any new messages that have been sent...
     * 
     * @param type $game_id
     * @param type $time
     * @return type 
     */
    function get_new($time)
    {
        $this->db->select('public_chat.*, users.username');
        $this->db->join('users', 'users.id=public_chat.user_id');
        $this->db->where('time_stamp >', $time);
        $this->db->order_by('time_stamp', 'desc');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get a list of active chat users
     */
    function get_users()
    {
        // Get the current time minus 5 minutes
        $php_time = new DateTime();
        $php_time->modify('-5 minute');
        $time = $php_time->format('Y-m-d H:i:s');
        
        $this->db->select('users.username');
        $this->db->join('users', 'users.id=public_chat.user_id');
        $this->db->where('time_stamp >', $time);
        $this->db->group_by('users.id');
        $this->db->order_by('time_stamp', 'desc');
        return $this->db->get($this->table)->result();
        
    }  // end get_users
    
}  // end chatglobalmodel

?>

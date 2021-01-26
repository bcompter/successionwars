<?php

Class Gamemsgmodel extends MY_Model {
        
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'msg_id';
        $this->table = 'gamemsg';
    }
 
    /**
     * Get the last 10 messages in a given game
     * changed to 20...
     * @param type $game_id 
     */
    function get_last_ten($game_id)
    {        
        $this->db->where('game_id', $game_id);
        $this->db->limit(30);
        $this->db->order_by('msg_id', 'desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_last($game_id, $offset)
    {        
        $this->db->select('gamemsg.*');
        $this->db->where('gamemsg.game_id',$game_id);
        $this->db->limit(20, $offset);
        $this->db->order_by('timestamp', 'desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_num_logs($game_id)
    {
        $this->db->where('gamemsg.game_id',$game_id);
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
        $this->db->where('game_id',$game_id);
        $this->db->where('timestamp >', $time);
        $this->db->order_by('msg_id', 'desc');
        return $this->db->get($this->table)->result();
    }
    
}
    
?>

<?php

/**
 * Movement Log Model 
 * Used for handling requests to undo movement
 */
Class Movementlogmodel extends MY_Model 
{

    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'log_id';
        $this->table = 'movement_logs';
    }
    
    /**
     * Get the current movement order for this game
     */
    function get_movement_order($game_id)
    {
        $log = $this->db->query('SELECT * FROM movement_logs WHERE'
                . ' game_id='.$game_id
                . ' ORDER BY move_order desc LIMIT 1')->row();
        
        if (!isset($log->log_id))
            return 0;
        else
            return $log->move_order;
    }
    
    /**
     * Get all logs from a game with the specified order
     */
    function get_logs_by_order($game_id, $order)
    {
        return $this->db->query('SELECT * FROM movement_logs WHERE '
                . 'game_id='.$game_id.' AND '
                . 'move_order='.$order)->result();
    }
    
    /**
     * Get the jumpship log from a game with the specified order
     */
    function get_jumpship_log_by_order($game_id, $order)
    {
        return $this->db->query('SELECT * FROM movement_logs WHERE '
                . 'game_id='.$game_id.' AND '
                . 'object_type="jumpship" AND '
                . 'move_order='.$order)->row();
    }
    
    /**
     * Delete all movement logs in a game.
     * Used after movement is complete
     */
    function delete_by_game($game_id)
    {
        $this->db->query('DELETE FROM movement_logs WHERE game_id='.$game_id);
    }
}
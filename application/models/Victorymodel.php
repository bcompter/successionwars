<?php

/**
 * Handles alternate victory conditions
 */
Class Victorymodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'condition_id';
        $this->table = 'victory_conditions';
    }
    
    /**
     * Get all victory conditions by game
     */
    function get_by_game($game_id)
    {
        return $this->db->query('SELECT victory_conditions.*, players.faction FROM victory_conditions '
                . 'JOIN players on players.player_id=victory_conditions.player_id '
                . 'WHERE victory_conditions.game_id='.$game_id
                . '')->result();
    }
    
    /**
     * Get all victory conditions by player
     * Include all conditions for everyone as well, which are denoted by a player Id of 0
     */
    function get_by_player($player_id)
    {
        return $this->db->query('SELECT victory_conditions.* FROM victory_conditions '
                . 'JOIN games on games.game_id=victory_conditions.game_id '
                . 'WHERE victory_conditions.player_id='.$player_id.' '
                . 'OR victory_conditions.player_id=0')->result();
    }
    
}
<?php

/*
 * Admin swapping
 */

Class Player_admin_swap_model extends MY_Model {

    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'swap_id';
        $this->table = 'player_admin_swap';
    }
    
    /**
     * Check if asdmin is swapped in this game
     * @param type $admin_user_id, $game_id
     * @return TRUE / FALSE
     */
    function check_by_admin_user_id($admin_user_id, $game_id)
    {
        $this->db->where('game_id', $game_id);
        $this->db->where('admin_user_id', $admin_user_id);
        $c = $this->db->count_all_results('player_admin_swap') == 1;
        if ($c)
            return TRUE;
        else
            return FALSE;
    }
    
    function get_by_player_id($swap_with_player_id)
    {
        $this->db->where('player_id', $swap_with_player_id);
        //$this->db->where('users.email_on_private_message', 1);
        //$this->db->join('users','players.user_id=users.id', 'left');
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_gamesswapped_by_user_id($user_id)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        //$this->db->join('users', 'users.id=player_admin_swap.admin_user_id');
        //$this->db->join('games', 'games.game_id=users.game_id');
        $this->db->join('games', 'games.game_id = player_admin_swap.game_id');
        $this->db->where('player_admin_swap.admin_user_id', $user_id);
        //return $this->db->get($this->table)->result();
        return $this->db->get()->result();
    }    
    
}

?>
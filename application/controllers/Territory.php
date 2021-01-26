<?php

/**
 * Territory controller
 */

class Territory extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    } 
    
    /**
     * Change the owner of a territory
     * Admin only
     */
    function change_owner($territory_id=0, $player_id=-1)
    {
        $page = $this->page;

        // Make sure an id is provided
        if ( $territory_id == 0 )
        {
            $page['error'] = 'No such territory!';
            $this->load->view('template', $page);
            return;
        }
        
        // Fetch the territory
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($territory_id);
        $game_id = $territory->game_id;
        
        // Make sure the territory exists
        if ( count($territory) != 1 ) 
        {
            $page['error'] = 'No such territory!!';
            $this->load->view('template', $page);
            return;
        }
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( count($page['game']) != 1 )
        {
            $page['error'] = 'No such game!';
            $this->load->view('template', $page);
            return;
        }
        
        // Admin only
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Check for a provided player id
        $this->load->model('playermodel');
        if ($player_id == -1)
        {
            // Prep and show the view
            $page['players']    = $this->playermodel->get_by_game($game_id);
            $page['territory']  = $territory;
            $page['content']    = 'territory_change_owner';
            $this->load->view('template', $page);
            return;
        }
        
        // Check to make sure the new owner is legit
        $player = $this->playermodel->get_by_id($player_id);
        if (!isset($player->player_id))
        {
            $page['error'] = 'No such player!';
            $this->load->view('template', $page);
            return;
        }
        if ($game_id != $player->game_id && $player_id != 0)
        {
            $page['error'] = 'Selected player is not in this game!';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go!
        $tupdate = new stdClass();
        $tupdate->player_id = $player_id;
        $this->territorymodel->update($territory_id, $tupdate);
        
        $this->session->set_flashdata('notice', 'Territory Updated!');
        redirect('game/view_admin_map/'.$game_id);
        
    }  // end change_owner
    
    /**
     * Toggle the contested flag on a territory
     * Admin only...
     */
    function toggle_contested($territory_id=0)
    {
        $page = $this->page;

        // Make sure an id is provided
        if ( $territory_id == 0 )
        {
            $page['error'] = 'No such territory!';
            $this->load->view('template', $page);
            return;
        }
        
        // Fetch the territory
        $this->load->model('territorymodel');
        $temp['territory'] = $this->territorymodel->get_by_id($territory_id);
        $game_id = $temp['territory']->game_id;
        
        // Make sure the territory exists
        if ( count($temp['territory']) != 1 ) 
        {
            $page['error'] = 'No such territory!!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( count($page['game']) != 1 )
        {
            $page['error'] = 'No such game!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Admin only
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Away we go!
        $tupdate = new stdClass();
        $tupdate->is_contested = $temp['territory']->is_contested ^ 1;
        $this->territorymodel->update($territory_id, $tupdate);
        
        // Create Combat logs
        $location = $this->territorymodel->get_by_id($territory_id);        
        $game = $this->gamemodel->get_by_id($game_id);          
        if ($tupdate->is_contested) // Only generate logs if being toggled to is_contested==TRUE
            // Generate combat logs and auto-assign targets as needed
            generate_combat_logs($location, $game);
        
        $this->session->set_flashdata('notice', 'Territory Updated!');
        redirect('game/view_admin_map/'.$game_id);
        
    }  // end toggle_contested
    
}  // end territory

<?php

/*
 * Handles Periphery views and bidding 
 * 
 */

class Periphery extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    } 
    
    function view($game_id=0)
    {
        $page = $this->page;
        
        // Id must be provided
        if ($game_id == 0)
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the game
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'No such game!';
            $this->load->view('templatexml', $page);
            return;
        }        
        
        // Must be playing in the game
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($game_id);
        foreach($players as $p)
        {
            if ($p->user_id == $this->page['user']->id)
                $player = $p;
        }
        if (!isset($player->player_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go
        $this->load->model('territorymodel');
        $page['periphery'] = $this->territorymodel->get_by_game_periphery($game_id);
        
        $this->load->model('peripherymodel');
        $page['has_open_nations'] = false;
        foreach($page['periphery'] as $p)
        {
            if (count ($this->peripherymodel->get_by_territory($p->territory_id)) == 0 )
                $page['isopen'][] = false;
            else
            {
                $page['isopen'][] = true;
                $page['has_open_nations'] = true;
            }
        }
        
        $page['content'] = 'viewperiphery';
        $this->load->view('templatexml', $page);
    }
    
    /**
     * Bid for or open bidding on a periphery nation
     * 
     * @param type $territory_id The periphery nation to bid on
     */
    function bid($territory_id=0, $offer=-1)
    {
        // Id must be provided
        if ($territory_id == 0)
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the territory
        $this->load->model('territorymodel');
        $periphery = $this->territorymodel->get_by_id($territory_id);
        if (!isset($periphery->is_periphery))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        if (!$periphery->is_periphery)
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the game
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($periphery->game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'No such game!';
            $this->load->view('templatexml', $page);
            return;
        }        
        
        // Must be playing in the game
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($game->game_id);
        foreach($players as $p)
        {
            if ($p->user_id == $this->page['user']->id)
                $player = $p;
        }
        if (!isset($player->player_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        
        
        // Away we go
        
        // Check for input
        if ($offer == -1)
        {
            // Must not have already bid
            $this->load->model('peripherymodel');
            $bids = $this->peripherymodel->get_by_territory($territory_id);
            foreach($bids as $b)
            {
                if ($b->player_id == $player->player_id && isset($b->offer))
                {
                    $page['error'] = 'You have already bid!';
                    $this->load->view('templatexml', $page);
                    return;
                }   
            }
            
            // Display form
            $page['content'] = 'peripherybid';
            $page['periphery'] = $periphery;
            $this->load->view('templatexml', $page);
        }
        else
        {
            // Process request
            // 
            // Must be able to afford the bid
            if ($player->money < $offer)
            {
                $page['error'] = 'You Can\'t afford it!';
                $this->load->view('templatexml', $page);
                return;
            }  
            
            periphery_bid($player, $periphery, $offer, $game, false);        
            
            $this->page['notice'] = 'Bid submitted!';
            $this->view($game->game_id);
        }
    }
    
    /**
     * Delete a periphery bid
     * @param type $id
     */
    function delete($id=0, $game_id = 0)
    {
        // Check and verify input
        if ($id==0)
        {
            $page['error'] = 'Invalid entry';
            $this->load->view('templatexml', $page);
        }
        
        // Admin ONLY!
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        
        // Grab the entry
        $this->load->model('peripherymodel');
        $bid = $this->peripherymodel->get_by_id($id);
        if (!isset($bid->player_id))
        {
            $page['error'] = 'Invalid entry';
            $this->load->view('templatexml', $page);
        }
        
        // Delete and return
        $this->peripherymodel->delete($id);
        $this->session->set_flashdata('notice', 'Periphery Bid Deleted!');
        redirect('game/view_admin/'.$game_id, 'refresh');
        
    }  // end delete

    
}  // end periphery